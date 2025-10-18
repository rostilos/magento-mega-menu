<?php
namespace Rostilos\MegaMenu\Block\Adminhtml\Item;

class ImportCategories extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => ['id' => 'frm-import-categories',
                'class' => 'admin__scope-old']
            ]
        );

        $form->setUseContainer(true);
        $form->addField('import_categories_messages', 'note', []);
        $fieldSet = $form->addFieldset('frm-import-categories-fieldset', []);

        $fieldSet->addField(
            'import_type',
            'select',
            [
                'label' => __('Select Import Type'),
                'title' => __('Select Import Type'),
                'name' => 'import_type',
                'required' => true,
                'options' => [
                    1 => __('Import all categories which activated and included in menu'),
                    2 => __('Specify specific categories to import')
                ],
                'note' => __('If you import all categories, only Categories with'
                    . ' “Enable Category” and ’Include in Menu’ enabled are imported.')
            ]
        );
        $fieldSet->addField(
            'category_ids',
            'select',
            [
                'label' => __('Select Categories'),
                'title' => __('Select Categories'),
                'required' => false,
                'options' => [],
                'class' => 'validate-category',
                'name' => 'category_ids',
                'note' => __('Select the child of the Root Category to import.')
            ]
        );

        //get menu item options
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $om->get('Rostilos\MegaMenu\Model\Item');
        $menuGroupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        $itemOptions = $model->getMenuItemOptions($menuGroupId);
        //rename blank item
        $itemOptions[0] = __('-- Top --');
        $fieldSet->addField(
            'parent_id',
            'select',
            [
                'label' => __('Parent Item'),
                'title' => __('Select one'),
                'name' => 'parent_id',
                'required' => false,
                'options' => $itemOptions,
                'note' => __('Specify the parent menu item to which the selected categories are imported.')
            ]
        );

        $this->setForm($form);
    }

    /**
     * @return string
     */
    public function getWidgetOptions()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $menuGroupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        //get menu group
        $menuGroup = $om->create('Rostilos\MegaMenu\Model\Group');
        $menuGroup->load($menuGroupId);
        //get store ids of menu group
        $stores = $menuGroup->getStores();
        $storeId = isset($stores[0]) ? $stores[0] : null;

        $widgetOptions = [
            'suggestOptions' => [
                'source' => $this->getUrl('rsmegamenu/item/ajaxSuggestCategories', ['store_id' => $storeId]),
                'valueField' => '#category_ids',
                'className' => 'category-select',
                'multiselect' => true,
                'showAll' => true,
                'ajaxOptions' => [
                    'error' => ''
                ]
            ],
            'saveUrl' => $this->getUrl('rsmegamenu/item/ajaxImportCategories'),
        ];

        return $this->jsonEncoder->encode($widgetOptions);
    }

}
