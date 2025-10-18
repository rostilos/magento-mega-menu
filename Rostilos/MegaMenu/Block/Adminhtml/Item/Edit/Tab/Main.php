<?php
namespace Rostilos\MegaMenu\Block\Adminhtml\Item\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_categoryFactory = $categoryFactory;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /* @var $model \Rostilos\MegaMenu\Model\Item */
        $model = $this->_coreRegistry->registry('rsmegamenu_item');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Rostilos_MegaMenu::item_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        //get menu item options
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menuGroupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        $itemOptions = $model->getMenuItemOptions($menuGroupId);

        $isElementParentItemDisabled = false;
        if (!sizeof($itemOptions)) {
            $isElementParentItemDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('item_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Basic Settings')]);

        if ($model->getId()) {
            $fieldset->addField('item_id', 'hidden', ['name' => 'item_id']);
        }

        $fieldset->addField(
            'group_id',
            'select',
            [
                'label' => __('Menu Group'),
                'title' => __('Select one'),
                'name' => 'group_id',
                'required' => true,
                'options' => $model->getMenuGroupOptions(),
                'disabled' => true
            ]
        );

        $fieldset->addField(
            'parent_id',
            'select',
            [
                'label' => __('Parent Item'),
                'title' => __('Select one'),
                'name' => 'parent_id',
                'required' => false,
                'options' => $itemOptions,
                'disabled' => $isElementParentItemDisabled
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title of menu item'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'seo_title',
            'text',
            [
                'name' => 'seo_title',
                'label' => __('SEO Title'),
                'title' => __('SEO title of menu item'),
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'show_title',
            'select',
            [
                'label' => __('Show Title'),
                'title' => __('Show title'),
                'name' => 'show_title',
                'required' => true,
                'options' => $model->getShowTitleOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addType('image', 'Rostilos\MegaMenu\Block\Adminhtml\Item\Helper\Image');
        $fieldset->addField(
            'icon_image',
            'image',
            array(
                'name' => 'icon_image',
                'label' => __('Menu Icon'),
                'title' => __('The Icon Image of menu item to upload'),
                'note' => __('Allowed file types: jpg, jpeg, gif, png'),
                'required' => false,
                'disabled' => $isElementDisabled
            )
        );

        $note = " " . __('Fill in <a href="//fontawesome.com/icons?d=gallery&m=free" target="_blank"
rel="nofollow" title="Click to see more about Font-Awesome">Font-awesome</a> name.');
        $note .= " " . __('For instance: fa fa-home (<i class="fas fa-home"></i>).');
        $fieldset->addField(
            'font_awesome',
            'text',
            [
                'name' => 'font_awesome',
                'label' => __('Font Awesome'),
                'title' => __('Put The Font Awesome Class Here'),
                'note' => $note,
                'required' => false,
                'disabled' => $isElementDisabled
            ]
        );

        $linkType = $fieldset->addField(
            'link_type',
            'select',
            [
                'label' => __('Link Type'),
                'title' => __('Link type of the menu item'),
                'name' => 'link_type',
                'required' => true,
                'options' => $model->getLinkTypeOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        //custom link field
        $customLink = $fieldset->addField(
            'link',
            'text',
            [
                'name' => 'link',
                'label' => __('Menu Link'),
                'title' => __('Link of menu item'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        //select category field
        $categoryLink = $fieldset->addField(
            'category_id',
            'select',
            [
                'label' => __('Select Category'),
                'title' => __('Select Category'),
                'name' => 'category_id',
                'required' => true,
                'options' => $this->getStoreCategories(),
                'class' => 'validate-category',
                'disabled' => $isElementDisabled
            ]
        );
        //setting custom renderer for category field
        $renderer = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element'
        )->setTemplate(
            'Rostilos_MegaMenu::item/widget/form/renderer/fieldset/selectone.categories.phtml'
        );
        $categoryLink->setRenderer($renderer);

        $isShowThumb = $fieldset->addField(
            'is_show_category_thumb',
            'select',
            [
                'label' => __('Menu Thumbnail'),
                'title' => __('Menu Thumbnail'),
                'name' => 'is_show_category_thumb',
                'required' => false,
                'options' => $model->getIsShowThumb(),
                'note' => __("The category image will be used as the featured thumbnail of the menu item.")
            ]
        );

        $showNumberProduct = $fieldset->addField(
            'show_number_product',
            'select',
            [
                'label' => __('Show Number Product'),
                'title' => __('Show number product in menu title?'),
                'name' => 'show_number_product',
                'required' => false,
                'options' => $model->getShowNumberProductOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        //select cms page field
        $cmsLink = $fieldset->addField(
            'cms_page',
            'select',
            [
                'label' => __('Select CMS Page'),
                'title' => __('Select CMS page'),
                'name' => 'cms_page',
                'required' => true,
                'options' => [],
                'disabled' => $isElementDisabled
            ]
        );

        $linkTarget = $fieldset->addField(
            'link_target',
            'select',
            [
                'label' => __('Link Target'),
                'title' => __('Specify how to open the link of menu item'),
                'name' => 'link_target',
                'required' => true,
                'options' => $model->getLinkTargetOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status of menu item'),
                'name' => 'is_active',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
            $model->setData('cms_page_id', $model->getData('cms_page'));
        }

        if ($model->getData('icon_image')) {
            $model->setData('icon_image', $model->getData('icon_image'));
        }

        //$this->_eventManager->dispatch('adminhtml_rsmegamenu_item_edit_tab_main_prepare_form', ['form' => $form]);
        $form->setValues($model->getData());
        $this->setForm($form);

        // field dependencies
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                $linkType->getHtmlId(),
                $linkType->getName()
            )->addFieldMap(
                $isShowThumb->getHtmlId(),
                $isShowThumb->getName()
            )->addFieldMap(
                $customLink->getHtmlId(),
                $customLink->getName()
            )->addFieldMap(
                $categoryLink->getHtmlId(),
                $categoryLink->getName()
            )->addFieldMap(
                $cmsLink->getHtmlId(),
                $cmsLink->getName()
            )->addFieldMap(
                $showNumberProduct->getHtmlId(),
                $showNumberProduct->getName()
            )->addFieldDependence(
                $customLink->getName(),
                $linkType->getName(),
                \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CUSTOM
            )->addFieldDependence(
                $isShowThumb->getName(),
                $linkType->getName(),
                \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY
            )->addFieldDependence(
                $categoryLink->getName(),
                $linkType->getName(),
                \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY
            )->addFieldDependence(
                $cmsLink->getName(),
                $linkType->getName(),
                \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CMS
            )->addFieldDependence(
                $showNumberProduct->getName(),
                $linkType->getName(),
                \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY
            )
        );

        return parent::_prepareForm();
    }

    /**
     * @return array
     */
    public function getStoreCategories()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //get again menu group_id from session
        $menuGroupId = $objectManager->get('Magento\Backend\Model\Session')->getMenuGroupId();
        //get menu group
        $menuGroup = $objectManager->create('Rostilos\MegaMenu\Model\Group');
        $menuGroup->load($menuGroupId);
        //get store ids of menu group
        $stores = $menuGroup->getStores();
        $storeId = isset($stores[0]) ? $stores[0] : null;

        //get root category id of this store
        $store = $this->_storeManager->getStore($storeId);
        $rootCategoryId = $store->getRootCategoryId();
        if ($store->getId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $rootCategoryId = $this->_storeManager->getDefaultStoreView()->getRootCategoryId();
        }

        $category = $this->_categoryFactory->create();
        $recursionLevel = max(
            0,
            (int)$this->scopeConfig->getValue(
                'catalog/navigation/max_depth',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $categories = $category->getCategories(
            $rootCategoryId,
            $recursionLevel,
            false,
            false,
            true
        );

        //build tree items
        $items = [];
        if ($nodes = $categories->getNodes()) {
            $hasParent = false;
            /* @var $nodes \Magento\Framework\Data\Tree\Node[] */
            foreach ($nodes as $node) {
                $level = 1;
                // add parent root
                if (!$hasParent) {
                    $hasParent = true;
                    $parentId = $node->getParent()->getData('entity_id');

                    /* @var $parent \Magento\Catalog\Model\Category */
                    $parent = \Magento\Framework\App\ObjectManager::getInstance()
                        ->get('Magento\Catalog\Model\Category')
                        ->setStoreId($storeId)
                        ->load($parentId);

                    if ($parent->getId()) {
                        $items[$parent->getId()] = $parent->getName();
                    }
                }

                // add child node
                $items[$node->getData('entity_id')] = $this->getSpaces($level, $node->getData('name'));
                if ($node->hasChildren()) {
                    $this->renderChildrenNode($node->getChildren(), $level, $items);
                }
            }
        }

        return $items;
    }

    /**
     * @param $level
     * @param $name
     * @return string
     */
    public function getSpaces($level, $name)
    {
        $spaces = $level * 8;
        return str_repeat(' ', $spaces) . $name;
    }

    /**
     * @param $nodes
     * @param int $level
     * @param array $items
     */
    public function renderChildrenNode($nodes, $level, &$items)
    {
        $level += 1;
        /* @var $nodes \Magento\Framework\Data\Tree\Node[] */
        foreach ($nodes as $node) {
            $items[$node->getData('entity_id')] = $this->getSpaces($level, $node->getData('name'));

            if ($node->hasChildren()) {
                $this->renderChildrenNode($node->getChildren(), $level, $items);
            }
        }
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Basic Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Basic Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get selected category option
     *
     * @return array
     */
    protected function _getSelectedCategoryOption($categoryId = null)
    {
        $result = [];
        if ($categoryId) {
            $collection = $this->_categoryFactory->create()->getCollection()->addAttributeToSelect(
                'name'
            )->addAttributeToSort(
                'entity_id',
                'ASC'
            )->addAttributeToFilter('entity_id', ['eq', $categoryId])->setPageSize(1);
            $items = $collection->load()->getItems();
            if ($items) {
                $item = array_pop($items);
                $result = [$item->getEntityId() => $item->getName()];
            }
        }

        return $result;
    }
}
