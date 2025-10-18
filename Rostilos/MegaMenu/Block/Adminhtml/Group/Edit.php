<?php
namespace Rostilos\MegaMenu\Block\Adminhtml\Group;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

class Edit extends Container
{
    protected $_coreRegistry = null;

    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'group_id';
        $this->_blockGroup = 'Rostilos_MegaMenu';
        $this->_controller = 'adminhtml_group';

        parent::_construct();

        //add button manage items
        $groupId = $this->_coreRegistry->registry('rsmegamenu_group')->getId();
        if ($groupId) {
            $this->buttonList->add(
                'manageitems',
                [
                    'label' => __('Manage Menu Items'),
                    'class' => 'manage__items',
                    'onclick' => 'window.location.href=\''
                        . $this->getUrl('rsmegamenu/item/index', ['group_id' => $groupId ]) . '\'',
                ],
                -1,
                10
            );
        }

        if ($this->_isAllowedAction('Rostilos_MegaMenu::group_save')) {
            $this->buttonList->update('save', 'label', __('Save Menu Group'));
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

        if ($this->_isAllowedAction('Rostilos_MegaMenu::group_delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Menu Group'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('rsmegamenu_group')->getId()) {
            return __(
                "Edit Menu Group '%1'",
                $this->escapeHtml($this->_coreRegistry->registry('rsmegamenu_group')->getTitle())
            );
        } else {
            return __('New Menu Group');
        }
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            'rsmegamenu/*/save',
            ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']
        );
    }
}
