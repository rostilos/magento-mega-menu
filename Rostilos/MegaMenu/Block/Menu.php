<?php

namespace Rostilos\MegaMenu\Block;

use Magento\Customer\Model\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Rostilos\MegaMenu\Helper\Cache as CacheHelper;
use Rostilos\MegaMenu\Helper\Data as DataHelper;
use Rostilos\MegaMenu\Helper\Mega;
use Rostilos\MegaMenu\Model\Group;
use Rostilos\MegaMenu\Model\Item;
use Rostilos\MegaMenu\Helper\Mega as MegaHelper;
use Rostilos\Base\Helper\Data as BaseHelper;

class Menu extends Template implements IdentityInterface
{
    public array $_configs = [
        'is_mega_menu' => 1,
        'is_main_menu' => 0,
        'is_mobile_menu' => 0,
        'show_menu_title' => 0,
        'show_number_product' => 0,
        'mega_style' => 1,
        'default_mega_col_width' => 200,
        'mega_col_margin' => 20,
        'mega_content_visible_option' => null,
        'mega_content_visible_in' => null,
        'start_level' => 0,
        'end_level' => 10,
        'menu_position' => null,
        'menu_group_id' => null,
        'menu_key' => null,
        'animation' => null,
        'addition_class' => null,
        'cache_lifetime' => 86400,
    ];

    protected $store;

    protected BaseHelper $baseHelper;
    protected DataHelper $dataHelper;

    protected MegaHelper $megaHelper;
    protected CacheHelper $cacheHelper;

    protected SerializerInterface $serializer;
    protected HttpContext $httpContext;
    private EventManager $eventManager;

    private StoreManagerInterface $storeManager;

    public function __construct(
        TemplateContext       $context,
        HttpContext           $httpContext,
        BaseHelper            $baseHelper,
        DataHelper            $dataHelper,
        MegaHelper            $megaHelper,
        CacheHelper           $cacheHelper,
        EventManager          $eventManager,
        StoreManagerInterface $storeManager,
        array                 $data = []
    )
    {
        $this->baseHelper = $baseHelper;
        $this->httpContext = $httpContext;
        $this->dataHelper = $dataHelper;
        $this->megaHelper = $megaHelper;
        $this->cacheHelper = $cacheHelper;
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * @return Template
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function _beforeToHtml(): Template
    {
        //initial configs
        $this->initialConfig($this->getData());

        //get menu group id
        if ($this->hasData('menu_id')) {
            $menuGroupId = $this->getData('menu_id');
            /** @var Group $menuGroup */
            $menuGroup = $this->dataHelper->getMenuGroup($menuGroupId);
            $menuKey = $menuGroup->getIdentifier();
        } else {
            //get menu key from config
            $menuKey = ($this->hasData('menu_key')) ? trim($this->getData('menu_key')) : null;
            $customerGroupId = $this->httpContext->getValue(Context::CONTEXT_GROUP);
            //get menu group id by menu key and customer group id
            $menuGroup = $this->dataHelper->getMenuGroup(0, $menuKey, $customerGroupId);
            $menuGroupId = $menuGroup->getId();
        }

        //update some other configs
        $this->_configs['menu_title'] = $menuGroup->getTitle();
        $this->_configs['menu_key'] = $menuKey;
        $this->_configs['menu_group_id'] = $menuGroupId;
        $this->_configs['menu_position'] = $menuGroup->getMenuPosition();
        if ($this->_configs['menu_position'] === 'main') {
            $this->_configs['is_main_menu'] = 1;
        }
        $this->_configs['menu_type'] = $menuGroup->getMenuType();
        if ($this->_configs['menu_type'] == Group::TYPE_VERTICAL
            || $this->_configs['menu_type'] == Group::TYPE_HORIZONTAL) {
            $mobileType = $menuGroup->getMobileType();
        } else {
            $mobileType = $menuGroup->getMenuType();
        }
        $this->_configs['mobile_type'] = $mobileType;

        $this->_configs['animation'] = ($this->hasData('animation'))
            ? trim($this->getData('animation'))
            : $menuGroup->getAnimationType();

        //set config params for mega helper
        $this->megaHelper->setParams($this->_configs);

        return parent::_beforeToHtml();
    }

    /**
     * @return string
     * @throws ValidatorException
     */
    protected function _toHtml(): string
    {
        //assign template
        if (!$this->getTemplate()) {
            $this->setTemplate("Rostilos_MegaMenu::menu-component.phtml");
            $this->setArea('frontend');
        }

        //get menu items and generate menu items tree html
        if ($this->_configs['menu_group_id'] && $this->_configs['menu_key']) {
            $menuHtml = $this->_generateMenuHtml($this->_configs['menu_group_id']);
        } else {
            if ($this->_configs['menu_key']) {
                $menuHtml = '<div class="no-menu">'
                    . __(
                        'The menu with the "%1" key does not exist or has not been assigned to this store view.',
                        $this->_configs['menu_key']
                    ) . '</div>';
            } else {
                $menuHtml = '<span class="no-menu">'
                    . __('You have not set the menu to show in this store view yet.')
                    . '</span>';
            }
        }

        //assign data to template
        $this->assign('menuHtml', $menuHtml);
        $this->assign('deviceType', $this->getDeviceType());
        $this->assign('config', $this->_configs);

        return $this->fetchView($this->getTemplateFile());
    }

    protected function _generateMenuHtml($menuGroupId)
    {
        $html = null;
        $cacheVars = $this->getCacheVars();
        $cacheId = $this->cacheHelper->getId(
            '_generateMenuHtml',
            $cacheVars
        );
        $html = $this->cacheHelper->load($cacheId);
        if (!$html) {
            //get menu items and build menu markup html
            $items = $this->dataHelper->getMenuItems($menuGroupId, $this->_configs);
            if ($items) {
                //build menu items data
                $this->megaHelper->rebuildData($items, $this->getDeviceType());
                //generate menu
                $html = $this->megaHelper->genMenu(0, 0, $this->getDeviceType());
            } else {
                $html = '<span class="no-menu">' . __('There are not menu items found.') . '</span>';
            }
            //save to cache
            $this->cacheHelper->save(
                $this->baseHelper->getSerializer()->serialize($html),
                $cacheId, $this->_configs['cache_lifetime']
            );
        } else {
            $html = $this->baseHelper->getSerializer()->unserialize($html);
        }

        return $html;
    }

    /**
     * @param $data
     * @return $this
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    protected function initialConfig($data): static
    {
        foreach ($this->_configs as $key => $val) {
            $this->_configs[$key] = $this->baseHelper->getConfigValueByKey($key, ['rsmegamenu'], $data);
        }

        //init cache lifetime for custom cache
        $this->_configs['cache_lifetime'] = ($this->getData('cache_lifetime'))
            ? $this->getData('cache_lifetime')
            : $this->_configs['cache_lifetime'];

        return $this;
    }

    /**
     * @return array|string[]
     */
    public function getIdentities(): array
    {
        return [
            Store::CACHE_TAG,
            Group::CACHE_TAG,
            Item::CACHE_TAG,
            $this->_configs['menu_group_id'],
            $this->_configs['menu_key'],
            $this->httpContext->getVaryString(),
            $this->getDeviceType()
        ];
    }

    /**
     * @return array
     */
    public function getCacheVars(): array
    {
        $vars = [
            $this->_design->getDesignTheme()->getId(),
            $this->getTemplate(),
            $this->getNameInLayout(),
            $this->_configs['menu_group_id'],
            $this->httpContext->getVaryString(),
            $this->getDeviceType()
        ];

        return $vars;
    }

    /**
     * @return array
     */
    public function getCacheKeyInfo(): array
    {
        return $this->getCacheVars();
    }


    public function query(string $queryIdentifier, string $query, array $eventParams = []): string
    {
        $container = new DataObject(['query' => $query]);
        $params = array_merge($eventParams, ['gql_container' => $container]);
        $this->eventManager->dispatch('rs_graphql_render_before_' . $queryIdentifier, $params);

        return $container->getData('query');
    }


    /**
     * @param $menuGroup
     * @return mixed
     */
    public function getMenuIdentifier($menuGroup)
    {
        $area = $this->getArea();
        if ($area == 'graphql') {
            $result = $this->getData('graphRequest')['menu_key'];
        } else {
            $result = $menuGroup->getIdentifier();
        }

        return $result;
    }

    public function getStore()
    {
        if (!$this->store) {
            $this->store = $this->storeManager->getStore();
        }
        return $this->store;
    }

    public function getStoreCode()
    {
        return $this->getStore()->getCode();
    }
}
