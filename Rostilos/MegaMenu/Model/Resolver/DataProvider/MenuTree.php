<?php
declare(strict_types=1);

namespace Rostilos\MegaMenu\Model\Resolver\DataProvider;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Model\Template\FilterEmulate;
use Rostilos\MegaMenu\Api\GroupRepositoryInterface;
use Rostilos\MegaMenu\Helper\Cache as CacheHelper;
use Rostilos\MegaMenu\Model\ItemFactory;

/**
 * Menu Items Tree data provider
 */
class MenuTree
{
    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CacheHelper
     */
    protected $cacheHelper;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * MenuTree constructor.
     * @param GroupRepositoryInterface $groupRepository
     * @param ItemFactory $itemFactory
     * @param FilterEmulate $widgetFilter
     * @param ScopeConfigInterface $scopeConfig
     * @param CacheHelper $cacheHelper
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        ItemFactory $itemFactory,
        FilterEmulate $widgetFilter,
        ScopeConfigInterface $scopeConfig,
        CacheHelper $cacheHelper
    ) {
        $this->groupRepository = $groupRepository;
        $this->itemFactory = $itemFactory;
        $this->widgetFilter = $widgetFilter;
        $this->scopeConfig = $scopeConfig;
        $this->cacheHelper = $cacheHelper;
    }

    public function getMenuItems($menuKey, $parentId = 0)
    {
        $cacheId = $this->cacheHelper->getId(
            'getMenuItems',
            [
                $menuKey,
                $parentId
            ]
        );
        $result = $this->cacheHelper->load($cacheId);
        if (!$result) {
            //check existing of menu key
            $menuGroup = $this->groupRepository->getByMenuKey($menuKey);
            if (!$menuGroup->getId() || !$menuGroup->isActive()) {
                throw new NoSuchEntityException(
                    __('The Menu Group with the menu key "%1" doesn\'t exist or it was disabled.', $menuKey)
                );
            }

            //get store id of menu group
            $storeIds = $menuGroup->getStores();
            $storeId = isset($storeIds[0]) ? $storeIds[0] : null;
            $items = [];

            //get menu items by menu group id
            $groupId = $menuGroup->getId();
            if ($groupId) {
                $maxLevel = $this->scopeConfig->getValue(
                    'rsmegamenu/general/end_level',
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $sortDir = \Magento\Framework\Data\Collection::SORT_ORDER_ASC;

                //get items collection
                $collection = $this->itemFactory->create()->getCollection()
                    ->addFieldToSelect('*')
                    ->addFieldToFilter('group_id', ['eq' => $groupId])
                    ->addFieldToFilter('level', ['lteq' => $maxLevel])
                    ->addFieldToFilter('is_active', ['eq' => \Rostilos\MegaMenu\Model\Item::STATUS_ENABLED]);
                if ($parentId) {
                    $collection->addFieldToFilter('path', ['like' => "%{$parentId}/%"]);
                }
                $collection->addOrder('level', $sortDir)
                    ->addOrder('sort_order', $sortDir)
                    ->addOrder('title', $sortDir)
                    ->load();

                //make tree items
                $ref = [];
                /* @var  \Rostilos\MegaMenu\Model\Item $item */
                foreach ($collection->getItems() as $item) {
                    $thisRef = &$ref[$item->getId()];
                    //rebuild data for storefront
                    $this->_buildItemData($item, $thisRef, $storeId);
                    if ($item->getParentId() == 0) {
                        $items[$item->getId()] = &$thisRef;
                    } else {
                        $ref[$item->getParentId()]['childs'][$item->getId()] = &$thisRef;
                    }
                }
                //if has specific a parent item
                $parentRef = [];
                $backItem = [
                    'id' => 0,
                    'title' => $menuGroup->getTitle(),
                ];
                if ($parentId) {
                    //load the menu item parent
                    $parentItem = $this->itemFactory->create()->load($parentId);
                    if ($parentItem) {
                        //load the parent item of parent item (using for back item)
                        if ($parentItem->getParentId()) {
                            $backMenuItem = $this->itemFactory->create()->load($parentItem->getParentId());
                            if ($backMenuItem) {
                                $backItem['id'] = $backMenuItem->getId();
                                $backItem['title'] = $backMenuItem->getTitle();
                            }
                        }

                        //rebuild data for storefront
                        $this->_buildItemData($parentItem, $parentRef, $storeId);

                        //append the parent item as view all item
                        $viewAllItem = $parentRef;
                        $lastMenuItemId = $this->itemFactory->create()->getCollection()->getLastItem()->getId();
                        $viewAllItem['id'] = ++$lastMenuItemId;
                        $viewAllItem['title'] = __("View all");
                        $viewAllItem['show_title'] = 1;
                        $viewAllItem['additional_class'] = "view_all";
                        $viewAllItem['mega_setting'] = [];
                        $items[$lastMenuItemId] = $viewAllItem;
                    }
                    if (isset($ref[$parentId]['childs'])) {
                        $items = array_merge($items, $ref[$parentId]['childs']);
                    }
                }
            }
            $result = [
                'menu_key' => $menuKey,
                'menu_title' => $menuGroup->getTitle(),
                'back_item' => $backItem,
                'items' => $items,
            ];
            //save to cache
            $cacheLifetime = $this->scopeConfig->getValue(
                'rsmegamenu/general/cache_lifetime',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $this->cacheHelper->save(
                $this->getSerializer()->serialize($result),
                $cacheId,
                $cacheLifetime
            );
        } else {
            $result = $this->getSerializer()->unserialize($result);
        }

        //return result
        return $result;
    }

    private function _buildItemData($item, &$thisRef, $storeId) {
        /* @var  \Rostilos\MegaMenu\Model\Item $item */
        $thisRef['id'] = $item->getId();
        $thisRef['title'] = $item->getTitle();
        $thisRef['show_title'] = $item->isShowTitle();
        $thisRef['parent'] = $item->getParentId();
        $thisRef['path'] = $item->getPath();
        $thisRef['level'] = $item->getLevel();
        $thisRef['icon_image'] = $item->getIconImage();
        $thisRef['link'] = $item->getLink();
        $thisRef['link_type'] = $item->getLinkType();
        if ($thisRef['link_type'] == \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY) {
            $thisRef['link'] = $this->_getCategoryUrlPath($item->getCategoryId(), $storeId);
        }
        $thisRef['is_active'] = $item->isActive();
        $thisRef['is_group'] = $item->isGroup();
        $thisRef['additional_class'] = $item->getAdditionClass();
        //Mega item's setting
        $megaSetting = [];
        $megaSetting['id'] = $item->getId();
        $megaSetting['column_number'] = $item->getMegaCols();
        $megaSetting['column_wrapper_width'] = $item->getMegaWidth();
        $megaSetting['column_x_width'] = $this->_convertColumnXWidth($item->getMegaColXWidth());
        $megaSetting['column_default_width'] = $this->scopeConfig->getValue(
            'rsmegamenu/general/default_mega_col_width',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $megaSetting['column_width_type'] = $item->getMegaBaseWidthType();
        $megaSetting['rich_content'] = $this->_getMegaContent($item);
        $megaSetting['description'] = ($item->getDescription()) ? $this->widgetFilter->filter($item->getDescription()) : null;
        //calculate for visibility of mega item's content
        $megaSetting['visibility'] = $item->getVisibleIn();
        $globalConfig = $this->scopeConfig->getValue(
            'rsmegamenu/general/mega_content_visible_in',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($item->getVisibleOption() == \Rostilos\MegaMenu\Model\Item::VISIBLE_OPTION_USE_GENERAL_CONFIG
            AND $globalConfig) {
            $megaSetting['visibility'] = $globalConfig;
        }

        $thisRef['mega_setting'] = $megaSetting;
    }

    private function _convertColumnXWidth($colXWidthSettings) {
        $rs = [];
        /**
         * Example format of $colXWidthSettings:
         * col1=50
         * col2=100
         * col3=50
         */
        if (!empty($colXWidthSettings)) {
            if (preg_match_all('/([^\s]+)=([^\s]+)/', $colXWidthSettings, $colWMatches)) {
                for ($i = 0; $i < count($colWMatches[0]); $i++) {
                    $attrCol = (string) $colWMatches[1][$i];
                    $rs[$attrCol] = $colWMatches[2][$i];
                }
            }
        }

        return json_encode($rs);
    }

    private function _getCategoryUrlPath($categoryId, $storeId = null) {
        $urlPath = '';
        if ($categoryId) {
            /* @var \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository*/
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryRepository = $om->get('Magento\Catalog\Api\CategoryRepositoryInterface');
            $category = $categoryRepository->get($categoryId, $storeId);
            if ($category) {
                $urlPath = $category->getUrlPath();
            }
        }

        return $urlPath;
    }

    private function _getMegaContent($item)
    {
        $content = null;
        /** @var \Rostilos\MegaMenu\Model\Item $item */
        $type = $item->getMegaSubContentType();
        if ($type == 'static-block') {
            $ids = $item->getStaticBlocks();
            $ids = preg_split('/,/', $ids);
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($id) {
                        $om = \Magento\Framework\App\ObjectManager::getInstance();
                        /** @var \Magento\Cms\Model\Block $blockModel */
                        $blockModel = $om->get('Magento\Cms\Model\Block')->load($id);
                        /** @var \Magento\Cms\Block\Block $block */
                        $block = $om->get('Magento\Framework\View\LayoutInterface')->createBlock('Magento\Cms\Block\Block');
                        $block->setBlockId($blockModel->getIdentifier());
                        $content .= $block->toHtml();
                    }
                }
            }
        } elseif ($type == 'custom-content') {
            $content = ($item->getCustomContent()) ? $this->widgetFilter->filter($item->getCustomContent()) : null;
        }

        return $content;
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     */
    private function getSerializer()
    {
        if ($this->serializer === null) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(SerializerInterface::class);
        }
        return $this->serializer;
    }
}
