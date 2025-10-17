<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Block\Adminhtml;

use Rostilos\MegaMenu\Model\Item\Image as ImageModel;

class TreeMenu extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Rostilos\MegaMenu\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var ImageModel
     */
    protected $imageModel;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Rostilos\MegaMenu\Model\ItemFactory $itemFactory
     * @param ImageModel $imageModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Rostilos\MegaMenu\Model\ItemFactory $itemFactory,
        ImageModel $imageModel,
        array $data = []
    ) {
        $this->_itemFactory = $itemFactory;
        $this->imageModel = $imageModel;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getMenuItems()
    {
        //get current menu group id
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $groupId = (int)$om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        $groupId = (int)$om->get('\Magento\Backend\App\Action\Context')->getRequest()->getParam('group_id', $groupId);

        $collection = $this->_itemFactory->create()->getCollection()
            ->addFieldToSelect(
                ['item_id', 'parent_id', 'title', 'icon_image', 'font_awesome', 'link', 'link_type', 'is_active', 'sort_order']
            )
            ->addFieldToFilter('group_id', ['in' => [$groupId]])
            ->addOrder('sort_order', 'ASC')
            ->addOrder('title', 'ASC')
            ->load();

        $ref = [];
        $items = [];
        /* @var  \Rostilos\MegaMenu\Model\Item $item */
        foreach ($collection->getItems() as $item) {
            $thisRef = &$ref[$item->getId()];

            $thisRef['parent'] = $item->getParentId();
            $thisRef['label'] = $item->getTitle();
            $thisRef['icon_image'] = $item->getIconImage();
            $thisRef['font_awesome'] = $item->getFontAwesome();
            $thisRef['link'] = $item->getLink();
            $thisRef['link_type'] = $item->getLinkTypeText();
            $thisRef['is_active'] = $item->isActive();
            $thisRef['id'] = $item->getId();

            if ($item->getParentId() == 0) {
                $items[$item->getId()] = &$thisRef;
            } else {
                $ref[$item->getParentId()]['child'][$item->getId()] = &$thisRef;
            }
        }

        return $items;
    }

    /**
     * @param int $parentId
     * @param array $items
     * @param string $class
     * @return string
     */
    public function buildTreeMenu($parentId = 0, $items = [], $class = 'dd-list')
    {
        $html = "<ol class=\"" . $class . "\" id=\"rostilos-mega-menu-{$parentId}\">";
        foreach ($items as $key => $value) {
            $icon = '';
            if (isset($value['font_awesome']) && $value['font_awesome']) {
                $icon = '<i class="' . $value['font_awesome'] . '"></i>';
            }
            if (isset($value['icon_image']) && $value['icon_image']) {
                $icon .= '<img class="menu-icon" src="'
                    . $this->imageModel->getBaseUrl() . $value['icon_image'] . '" /> ';
            }
            if ($value['is_active'])
                $btnChangeStatus = '<a class="change-status-button" id="change-'
                    . $value['id'] . '" data-id="'.$value['id'].'" title="'
                    . __('Disable this item') . '"><i class="fa fa-toggle-on"></i></a>';
            else
                $btnChangeStatus = '<a class="change-status-button" id="change-' . $value['id']
                    . '" data-id="'.$value['id'].'" title="' . __('Enable this item')
                    . '"><i class="fa fa-toggle-off"></i></a>';

            $html .= '<li class="dd-item dd3-item" data-id="' . $value['id'] . '" >
                    <div class="dd-handle dd3-handle"><i class="fa fa-move"></i></div>
                    <div class="dd3-content">
                        <span class="menu-item-title-tooltip">
                            <a href="javascript:void(0);" class="tooltip-toggle">
                                <span class="menu-item-title" id="label_show' . $value['id'] . '">'
                . $icon . strip_tags($value['label']) . '</span>
                            </a>
                            <span class="tooltip-content"><span class="label">'.__('Link Type').':</span> '
                .$value['link_type'].'<br/><span class="label">'.__('Menu Link').':</span> ' . $value['link'].'</span>
                        </span>
                        <span class="span-right sub-actions">
                            <a class="add-button" id="add-' . $value['id'] . '" data-id="'.$value['id']
                .'" title="' . __('Add sub item') . '" href="' . $this->getUrl(
                    'rsmegamenu/item/new',
                    ['parent_id' => $value['id']]
                ) . '" label="' . $value['label'] . '" link="'
                . $value['link'] . '" ><i class="fa fa-plus-circle"></i></a>
                            <a class="edit-button" id="edit-' . $value['id']
                . '" data-id="'.$value['id'].'" title="' . __('Edit this item')
                . '" href="' . $this->getUrl(
                    'rsmegamenu/item/edit', ['item_id' => $value['id']]
                ) . '" label="' . $value['label'] . '" link="' . $value['link'] . '" ><i class="fa fa-pencil"></i></a>
                            ' . $btnChangeStatus . '
                            <a class="del-button" id="delete-' . $value['id'] . '" data-id="'.$value['id']
                .'" title="' . __('Delete this item') . '"><i class="fa fa-trash"></i></a></span>
                    </div>';
            if (array_key_exists('child', $value)) {
                $html .= $this->buildTreeMenu($value['id'], $value['child']);
            }
            $html .= "</li>";
        }

        $html .= "</ol>";

        return $html;
    }

    /**
     * @return array
     */
    public function getMenuGroupOptions()
    {
        $item = $this->_itemFactory->create();
        $options = $item->getMenuGroupOptions();

        return $options;
    }
}
