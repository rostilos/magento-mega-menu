<?php

namespace Rostilos\MegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Rostilos\MegaMenu\Helper\Data as Helper;
use Rostilos\Base\Helper\Data as BaseHelper;

class CatalogCategorySaveAfterObserver implements ObserverInterface
{
    protected $baseHelper;

    protected $helper;

    protected $messageManager;

    public function __construct(
        BaseHelper $baseHelper,
        Helper $helper,
        MessageManager $messageManager
    ) {
        $this->baseHelper = $baseHelper;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isAllowed = (bool)$this->baseHelper->getConfigValueByKey('auto_sync_category_menu_item', ['rsmegamenu']);
        if (!$isAllowed) {
            return;
        }

        $category = $observer->getEvent()->getCategory();

        $parentId = $category->getParentId();

        if ($parentId == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
            return;
        }

        $relatedMenuItems = $this->helper->getRelatedMenuItems(
            \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY,
            ['category_ids' => [$parentId]],
            false
        );
        if ($relatedMenuItems) {
            foreach ($relatedMenuItems as $relatedMenuItem) {
                $item = $this->helper->getRelatedMenuItems(
                    \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY,
                    [
                        'category_ids' => [$category->getId()],
                        'parent_id' => $relatedMenuItem->getId()
                    ],
                    true
                );
                if (!$item->getId()) {
                    $this->addMenuItem($relatedMenuItem, $category);
                }
            }
            $this->messageManager->addWarningMessage(__('Menu items associated with this Category have been updated.'));
        }

        return $this;
    }

    public function addMenuItem($parentMenuItem, $category)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

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
        $data['sort_order'] = $category->getPosition();
        $data['parent_id'] = $parentMenuItem->getId();
        $data['group_id'] = $parentMenuItem->getGroupId();
        $data['link_type'] = \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CATEGORY;
        $data['link'] = 'dynamically';
        $data['category_id'] = $category->getId();
        $data['title'] = $category->getName();
        $data['identifier'] = trim(
            preg_replace(
                '/[^a-z0-9]+/',
                '-',
                strtolower($data['title'])
            ),
            '-'
        );
        $data['is_show_category_thumb'] = 0;

        return $om->create('Rostilos\MegaMenu\Model\Item')->setData($data)->save();
    }
}
