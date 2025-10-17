<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Controller\Adminhtml\Item;

class AjaxSortOrder extends \Magento\Backend\App\Action
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
        $menuData = $this->getRequest()->getParam('menu_data');
        $result = ['success' => false];

        if ($menuData) {
            try {
                //create menu item model
                $model = $this->_objectManager->create('Rostilos\MegaMenu\Model\Item');

                //decode menu data
                $menuData = $this->jsonDecoder->decode($menuData);

                //parse menu data to array
                $menuData = self::parseJsonArray($menuData);

                //load and save menu item data
                $i = 0;
                foreach ($menuData as $item) {
                    $i++;
                    $model->load($item['id']);
                    if ($model) {
                        $model->setParentId($item['parent_id']);
                        $model->setSortOrder($i);
                        $model->save();
                    }
                }

                $result['success'] =  true;
                $result['message'] = __('Menu items was saved.');

            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }

        $this->rawResult->setHeader('Content-type', 'application/json');
        return $this->rawResult->setContents($this->jsonEncoder->encode($result));
    }

    public static function parseJsonArray($jsonArray, $parentId = 0)
    {
        $rs = array();
        foreach ($jsonArray as $subArray) {
            $returnSubArray = array();
            if (isset($subArray['children'])) {
                $returnSubArray = self::parseJsonArray($subArray['children'], $subArray['id']);
            }

            $rs[] = array('id' => $subArray['id'], 'parent_id' => $parentId);
            $rs = array_merge($rs, $returnSubArray);
        }

        return $rs;
    }
}
