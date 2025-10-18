<?php
namespace Rostilos\MegaMenu\Block\Adminhtml\Group\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
        parent::_construct();
        $this->setId('group_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Menu Group'));
    }
}
