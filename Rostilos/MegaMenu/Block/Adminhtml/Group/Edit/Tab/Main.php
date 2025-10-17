<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Block\Adminhtml\Group\Edit\Tab;

/**
 * UB Mega Menu Group edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider
     */
    protected $customerGroups;

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
        \Magento\CatalogRule\Model\Rule\CustomerGroupsOptionsProvider $customerGroups,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->customerGroups = $customerGroups;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareForm()
    {
        /* @var $model \Rostilos\MegaMenu\Model\Group */
        $model = $this->_coreRegistry->registry('rsmegamenu_group');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Rostilos_MegaMenu::group_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('group_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Basic Information')]);

        if ($model->getId()) {
            $fieldset->addField('group_id', 'hidden', ['name' => 'group_id']);
        }

        $note = __('Depending on the system configuration (UB Mega Menu > General Settings > Display Off-canvas From) you set, available positions will show up here.');
        $note .= __(' More details <a href="//www.rostilos.com/docs/rostilos-mega-menu/#default" target="_blank" rel="nofollow">here</a>');
        $fieldset->addField(
            'menu_position',
            'select',
            [
                'name' => 'menu_position',
                'label' => __('Menu Position'),
                'title' => __('Select one'),
                'required' => true,
                'note' => $note,
                'options' => $model->getMenuPositions(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Menu Title'),
                'title' => __('Menu Title'),
                'note' => __('The title of Menu'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'identifier',
            'text',
            [
                'name' => 'identifier',
                'label' => __('Menu Identifier'),
                'title' => __('Menu Identifier'),
                'required' => true,
                'class' => 'validate-xml-identifier',
                'note' => __('Give your menu group an identifier which will be used to call the menu group'),
                'disabled' => $isElementDisabled
            ]
        );

        $note = __('Depending on the Menu Position you set, the menu types available will show up here.');
        $note .= __(' More details <a href="//www.rostilos.com/docs/rostilos-mega-menu/#default" target="_blank" rel="nofollow">here</a>');
        $fieldset->addField(
            'menu_type',
            'select',
            [
                'name' => 'menu_type',
                'label' => __('Menu Type'),
                'title' => __('Select one'),
                'required' => true,
                'note' => $note,
                'options' => $model->getAllMenuTypeOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        $note = __('Specify the menu type when the menu shows on Mobile. A horizontal menu or vertical menu will be transformed into either Accordion or Drill-down.');
        $fieldset->addField(
            'mobile_type',
            'select',
            [
                'name' => 'mobile_type',
                'label' => __('Menu on Mobile'),
                'title' => __('Select one'),
                'required' => true,
                'note' => $note,
                'options' => $model->getMobileTypeOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        $note = __('Animation effect you want to display sub-menu items. Currently, apply to the Horizontal menu only.');
        $fieldset->addField(
            'animation_type',
            'select',
            [
                'name' => 'animation_type',
                'label' => __('Animation Type'),
                'title' => __('Select one animation type'),
                'required' => true,
                'note' => $note,
                'options' => $model->getAnimationTypeOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'customer_group_id',
            'multiselect',
            [
                'name' => 'customer_groups[]',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'values' => $this->customerGroups->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true),
                    'disabled' => $isElementDisabled
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Menu Group Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $this->_eventManager->dispatch(
            'adminhtml_rsmegamenu_group_edit_tab_main_prepare_form',
            ['form' => $form]
        );

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Basic Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Basic Information');
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
}
