<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Controller\Adminhtml\Item;

class AjaxGetStaticBlockOptions extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Rostilos_MegaMenu::item_save';

    protected $jsonEncoder;

    protected $jsonDecoder;

    protected $rawResult;

    protected $storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Framework\Controller\Result\Raw $rawResult,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->rawResult = $rawResult;
        $this->storeManager = $storeManager;

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
     * Ajax get static block options action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $menuGroupId = $this->getRequest()->getParam('menu_group_id', null);
        $itemId = $this->getRequest()->getParam('item_id', null);
        $result = ['success' => false];

        if ($menuGroupId) {
            try {
                //get menu group
                $menuGroup = $this->_objectManager->create('Rostilos\MegaMenu\Model\Group')->load($menuGroupId);
                //get store ids of menu group
                $storeIds = $menuGroup->getStores();

                //get cms page options by store
                $helper = $this->_objectManager->get('\Rostilos\MegaMenu\Helper\Data');
                $options = $helper->getStaticBlockOptions($storeIds);

                // display success message
                $result['success'] = true;
                $result['message'] = __('Ok.');
                $result['options'] = $options;
                //add selected option
                if ($itemId) {
                    $menuItem = $this->_objectManager->create('Rostilos\MegaMenu\Model\Item')->load($itemId);
                    $blockIds = explode(',', $menuItem->getStaticBlocks());
                    $result['selected_options'] = $blockIds;
                } else {
                    $result['selected_options'] = [];
                }

            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        } else {
            // display error message
            $result['message'] = __('Let\'s specify a menu group.');
        }

        $this->rawResult->setHeader('Content-type', 'application/json');
        return $this->rawResult->setContents($this->jsonEncoder->encode($result));
    }
}
