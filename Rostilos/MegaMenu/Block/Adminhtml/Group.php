<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Block\Adminhtml;

/**
 * Adminhtml UB Mega Menu Groups content block
 */
class Group extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Block constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_group';
        $this->_blockGroup = 'Rostilos_MegaMenu';
        $this->_headerText = __('Manage Menu Groups');

        parent::_construct();

        if ($this->_isAllowedAction('Rostilos_MegaMenu::group_save')) {
            $this->buttonList->update('add', 'label', __('Add New Menu Group'));
        } else {
            $this->buttonList->remove('add');
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
}
