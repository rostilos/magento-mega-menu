<?php
namespace Rostilos\MegaMenu\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    const string ADMIN_RESOURCE = 'Rostilos_MegaMenu::group';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        //unset menu group id from session
        $this->_objectManager->get('Magento\Backend\Model\Session')->unsMenuGroupId();

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Rostilos_MegaMenu::group');
        $resultPage->addBreadcrumb(__('MegaMenu'), __('MegaMenu'));
        $resultPage->addBreadcrumb(__('Manage Menu Groups'), __('Manage Menu Groups'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Menu Groups'));

        return $resultPage;
    }
}
