<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Controller\Adminhtml\Item;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Rostilos_MegaMenu::item';

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
        //get current selected menu group and save to session to use in other context
        $menuGroupId = $this->getRequest()->getParam('group_id');
        if ($menuGroupId) {
            $this->_objectManager->get('Magento\Backend\Model\Session')->setMenuGroupId($menuGroupId);
        } else {
            $menuGroupId = $this->_objectManager->get('Magento\Backend\Model\Session')->getMenuGroupId();
        }
        if ($menuGroupId) {
            $model = $this->_objectManager->create('Rostilos\MegaMenu\Model\Group');
            $model->load($menuGroupId);
            $title = $model->getTitle()." ({$model->getIdentifier()})";
        } else {
            $title = null;
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Rostilos_MegaMenu::group');
        $resultPage->addBreadcrumb(__('UB Mega Menu'), __('UB Mega Menu'));
        $resultPage->addBreadcrumb(__('Manage Menu Items'), __('Manage Menu Items'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Menu Items of Menu: %1', $title));

        return $resultPage;
    }
}
