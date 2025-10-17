<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Controller\Adminhtml\Item;

class AjaxChangeStatus extends \Magento\Backend\App\Action
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
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('item_id');
        $result = ['success' => false];

        if ($id) {
            try {
                // init model
                $model = $this->_objectManager->create('Rostilos\MegaMenu\Model\Item');
                $model->load($id);

                //update status
                if ($model->isActive()) {
                    $model->setIsActive(false);
                    $result['action'] = 'disabled';
                } else {
                    $model->setIsActive(true);
                    $result['action'] = 'enabled';
                }

                //save status
                $model->save();

                // display success message
                $result['success'] = true;
                $result['message'] = __("The menu item has been {$result['action']}.");

            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        } else {
            // display error message
            $result['message'] = __('We can\'t find a menu item to change status.');
        }

        $this->rawResult->setHeader('Content-type', 'application/json');
        return $this->rawResult->setContents($this->jsonEncoder->encode($result));
    }
}
