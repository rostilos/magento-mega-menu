<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Controller\Adminhtml\Item;

class AjaxImportCategories extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Rostilos_MegaMenu::item_save';

    protected $jsonEncoder;

    protected $jsonDecoder;

    protected $rawResult;

    protected $storeManager;

    protected $categoryFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Framework\Controller\Result\Raw $rawResult,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->rawResult = $rawResult;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $importType = $this->getRequest()->getParam('import_type');
        $parentId = $this->getRequest()->getParam('parent_id');
        $categoryIds = $this->getRequest()->getParam('category_ids');
        $categoryIds = (!empty($categoryIds)) ? preg_split('/,\s*/', $categoryIds) : [];

        $result = [
            'success' => false,
            'menu_items' => ''
        ];

        try {
            //init some default menu item data
            $data = [];
            $data['show_title'] = \Rostilos\MegaMenu\Model\Item::SHOW_TITLE_YES;
            $data['icon_image'] = '';
            $data['font_awesome'] = '';
            $data['target'] = '_self';
            $data['show_number_product'] = \Rostilos\MegaMenu\Model\Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG;
            $data['cms_page'] = null;
            $data['is_group'] = \Rostilos\MegaMenu\Model\Item::IS_GROUP_NO;
            $data['mega_cols'] = 1;
            $data['mega_width'] = 0;
            $data['mega_col_width'] = 0;
            $data['mega_col_x_width'] = null;
            $data['mega_sub_content_type'] = \Rostilos\MegaMenu\Model\Item::SUB_CONTENT_TYPE_CHILD_ITEMS;
            $data['custom_content'] = null;
            $data['static_blocks'] = null;
            $data['addition_class'] = null;
            $data['description'] = null;
            $data['is_active'] = \Rostilos\MegaMenu\Model\Group::STATUS_ENABLED;
            $data['sort_order'] = 0;

            //get again menu group_id from session because we have disable this filed in form
            $menuGroupId = $this->_objectManager->get('Magento\Backend\Model\Session')->getMenuGroupId();
            $data['group_id'] = $menuGroupId;

            //get menu group
            $menuGroup = $this->_objectManager->create('Rostilos\MegaMenu\Model\Group');
            $menuGroup->load($menuGroupId);
            //get store ids of menu group
            $stores = $menuGroup->getStores();
            $storeId = isset($stores[0]) ? $stores[0] : null;

            //build categories tree from selected category ids
            $categoriesTree = $this->buildCategoriesTree($storeId, $importType, $categoryIds);
            //mapping category id - menu item id
            $mapping = [];

            //add menu items
            $this->addMenuItems($parentId, $categoriesTree, $data, $categoryIds, $mapping);

            //generate menu items in list view
            $result['menu_items'] = $this->buildTreeMenu($parentId, $categoriesTree);

            // display success message
            $result['success'] = true;
            $result['message'] = ($importType == 2)
                ? __("Selected categories was imported successfully.")
                : __("All %1 categories activated was imported successfully.", sizeof($mapping));

            $this->messageManager->addSuccessMessage($result['message']);
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $this->messageManager->addExceptionMessage($e, $result['message']);
        }

        $this->rawResult->setHeader('Content-type', 'application/json');
        return $this->rawResult->setContents($this->jsonEncoder->encode($result));
    }

    /**
     * @param int $storeId
     * @param $importType
     * @param array $selectedCategoryIds
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildCategoriesTree($storeId = 0, $importType = 1, $selectedCategoryIds = [])
    {
        $collection = $this->categoryFactory->create()->getCollection()
            ->addFieldToSelect(['entity_id', 'parent_id', 'name', 'level', 'position'])
            ->setStoreId($storeId);

        if ($importType == 1) { // import all
            //get root category id of this store
            $store = $this->storeManager->getStore($storeId);
            $rootCategoryId = $store->getRootCategoryId();
            if ($store->getId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
                $rootCategoryId = $this->storeManager->getDefaultStoreView()->getRootCategoryId();
            }
            //only get categories from root category of current store
            $collection->addFieldToFilter('path', ['like' => '%'.$rootCategoryId . '/%']);
            //$collection->addFieldToFilter('entity_id', ['neq' => $rootCategoryId]);

            //only get enabled categories
            $collection->addIsActiveFilter();
            //only get included in menu
            $collection->addAttributeToFilter('include_in_menu', 1);
        } elseif ($importType == 2 && $selectedCategoryIds) {
            $collection->addFieldToFilter('entity_id', ['in' => $selectedCategoryIds]);
        }

        $collection->getSelect()->order('level ASC');

        //build tree items
        $ref = [];
        $items = [];
        foreach ($collection->getItems() as $key => $category) {
            $selectedCategoryIds[] = $key;
            $thisRef = &$ref[$category->getId()];

            $thisRef['id'] = $category->getId();
            $thisRef['parent_id'] = $category->getParentId();
            $thisRef['name'] = $category->getName();
            $thisRef['link'] = 'dynamically';
            $thisRef['level'] = $category->getLevel();
            $thisRef['sort_order'] = $category->getPosition();

            if($category->getParentId() == 0 || (!in_array($category->getParentId(), $selectedCategoryIds))) {
                $items[$category->getId()] = &$thisRef;
            } else {
                $ref[$category->getParentId()]['child'][$category->getId()] = &$thisRef;
            }
        }

        return $items;
    }

    /**
     * @param null $parentMenuItemId
     * @param array $items
     * @param array $defaultData
     * @param array $categoryIds
     * @param $mapping
     * @return bool
     */
    public function addMenuItems(
        $parentMenuItemId = null,
        $items = [],
        $defaultData = [],
        $categoryIds = [],
        &$mapping = []
    ) {
        $itemIds = [];
        foreach ($items as $key => $item) {
            //make menu item data
            $data = $defaultData;
            $data['link_type'] = \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY;
            $data['link'] = $item['link'];
            $data['category_id'] = $item['id'];
            $data['title'] = $item['name'];
            $data['identifier'] = trim(
                preg_replace('/[^a-z0-9]+/', '-', strtolower($item['name'])),
                '-'
            );
            $data['sort_order'] = $item['sort_order'];

            //set parent menu item id
            if ($parentMenuItemId && $item['level'] == 2) {
                $data['parent_id'] = $parentMenuItemId;
            } else {
                if($categoryIds && !in_array($item['parent_id'], $categoryIds)) {
                    if ($parentMenuItemId) {
                        $data['parent_id'] = $parentMenuItemId;
                    }
                } else {
                    $data['parent_id'] = isset ($mapping[$item['parent_id']]) ? $mapping[$item['parent_id']] : 0;
                }
            }

            //create and save menu item
            $model = $this->_objectManager->create('Rostilos\MegaMenu\Model\Item')->setData($data)->save();

            //update mapping
            $mapping[$item['id']] = $model->getId();
            $itemIds[] = $model->getId();
            if(array_key_exists('child', $item)) {
                $this->addMenuItems($parentMenuItemId, $item['child'], $defaultData, $categoryIds, $mapping);
            }
        }
        //set cookie of items id
        setcookie("activeMenuItemIds",implode(",",$itemIds), time() + (86400 * 30), "/");

        return true;
    }

    /**
     * @param int $parentId
     * @param array $items
     * @param string $class
     * @return string
     */
    public function buildTreeMenu($parentId = 0, $items = [], $class = 'dd-list') {
        $html = ($parentId) ? "<ol class=\"".$class."\" id=\"rostilos-mega-menu-{$parentId}\">" : '';
        foreach($items as $key => $value) {
            $btnChangeStatus = '<a class="change-status-button" id="'.$value['id']
                .'" title="'.__('Disable this item').'"><i class="fa fa-toggle-on"></i></a>';
            $html.= '<li class="dd-item dd3-item" data-id="'.$value['id'].'" >
                    <div class="dd-handle dd3-handle"><i class="fa fa-move"></i></div>
                    <div class="dd3-content"><span class="menu-item-title" id="label_show'
                .$value['id'].'">' . $value['name'] .'</span>
                        <span class="span-right sub-actions">
                        <span id="link_show'.$value['id'].'">'.$value['link'].'</span> &nbsp;&nbsp;
                            <a class="add-button" id="'.$value['id'].'" title="'.__('Add sub item')
                .'" href="'.$this->getUrl('rsmegamenu/item/new', ['parent_id' => $value['id']])
                .'" label="'.$value['name'].'" link="'.$value['link'].'" ><i class="fa fa-plus-circle"></i></a>
                            <a class="edit-button" id="'.$value['id'].'" title="'.__('Edit this item')
                .'" href="'.$this->getUrl('rsmegamenu/item/edit', ['item_id' => $value['id']])
                .'" label="'.$value['name'].'" link="'.$value['link'].'" ><i class="fa fa-pencil"></i></a>
                            '.$btnChangeStatus.'
                            <a class="del-button" id="'.$value['id'].'" title="'.__('Delete this item')
                .'"><i class="fa fa-trash"></i></a></span>
                    </div>';
            if(array_key_exists('child', $value)) {
                $html .= $this->buildTreeMenu($value['id'], $value['child']);
            }
            $html .= "</li>";
        }

        if ($parentId) {
            $html .= "</ol>";
        }

        return $html;
    }

}
