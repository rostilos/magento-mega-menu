<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Block\Adminhtml\Item;

/**
 * Admin menu item
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize ubcs slide item edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'item_id';
        $this->_blockGroup = 'Rostilos_MegaMenu';
        $this->_controller = 'adminhtml_item';

        parent::_construct();

        if ($this->_isAllowedAction('Rostilos_MegaMenu::item_save')) {
            $this->buttonList->update('save', 'label', __('Save Menu Item'));
            $this->buttonList->update('save', 'title', __('Save Menu Item'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Rostilos_MegaMenu::item_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Menu Item'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('rsmegamenu_item')->getId()) {
            return __(
                "Edit Menu Item '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('rsmegamenu_item')->getTitle())
            );
        } else {
            return __('New Menu Item');
        }
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
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'rsmegamenu/*/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('item_custom_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'item_custom_content');
                    tinyMCE.execCommand('mceAddControl', false, 'item_description');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'item_custom_content');
                    tinyMCE.execCommand('mceRemoveControl', false, 'item_description');
                }
            };
        ";
        return parent::_prepareLayout();
    }
}
