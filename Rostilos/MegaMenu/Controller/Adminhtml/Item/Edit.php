<?php
namespace Rostilos\MegaMenu\Controller\Adminhtml\Item;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Rostilos_MegaMenu::item_save';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
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
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Rostilos_MegaMenu::item')
            ->addBreadcrumb(__('Rostilos Mega Menu'), __('Rostilos Mega Menu'))
            ->addBreadcrumb(__('Manage Menu Items'), __('Manage Menu Items'));
        return $resultPage;
    }

    /**
     * Edit Item
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('item_id');
        $parentId = $this->getRequest()->getParam('parent_id');

        $model = $this->_objectManager->create('Rostilos\MegaMenu\Model\Item');

        // 2. Initial checking
        if ($id) { //edit
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This menu item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);

        if (!$id) { // add new
            if (!$data) {
                $data = [];
            }
            $data['group_id'] = $this->_objectManager->get('Magento\Backend\Model\Session')->getMenuGroupId();
            $data['show_title'] = \Rostilos\MegaMenu\Model\Item::SHOW_TITLE_YES;
            $data['link_type'] = \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CUSTOM;
            $data['link'] = '#';
            $data['mega_cols'] = 1;
            $data['parent_id'] = $parentId;
        }

        if (!empty($data)) {
            $model->setData($data);
        }

        //set cookie of item id
        if ($model->getId()) {
            setcookie("activeMenuItemIds", $model->getId(), time() + (86400 * 30), "/");
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('rsmegamenu_item', $model);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Menu Item') : __('Add New Menu Item'),
            $id ? __('Edit Menu Item') : __('Add New Menu Item')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Menu Items'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('Add New Menu Item'));

        return $resultPage;
    }
}
