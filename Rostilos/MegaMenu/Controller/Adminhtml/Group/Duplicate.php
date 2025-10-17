<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;
use Rostilos\MegaMenu\Model\ItemFactory;

class Duplicate extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Rostilos_MegaMenu::group_duplicate';

    /**
     *
     * @var \Rostilos\MegaMenu\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @param Action\Context $context
     * @param ItemFactory $itemFactory
     */
    public function __construct(Action\Context $context, ItemFactory $itemFactory)
    {
        $this->_itemFactory = $itemFactory;
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
     * Duplicate action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        // get group id from request
        $id = $this->getRequest()->getParam('group_id');
        if ($id) {
            try {
                // load group by id
                $group = $this->_objectManager->create('Rostilos\MegaMenu\Model\Group')->load($id);

                // start clone
                $newGroup = $this->_objectManager->create('Rostilos\MegaMenu\Model\Group');
                $data = $group->getData();
                // clone group
                unset($data['group_id']);
                unset($data['creation_time']);
                unset($data['update_time']);
                $data['identifier'] = $data['identifier']."-".uniqid();
                $newGroup->setData($data);
                $newGroup->save();

                // clone items of group
                $this->findDuplicate(0, $group->getId(), 0, $newGroup->getId());

                // end clone, display success message
                $this->messageManager->addSuccess(__('The Menu Group and Menu Items of it has been duplicated.'));

                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a Menu Group to duplicate.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }

    private function findDuplicate($parentItemId, $groupId, $parentItemIdNew, $newGroupId) {
        $collection = $this->_itemFactory->create()->getCollection()
            ->addFieldToFilter('group_id', ['eq' => $groupId])
            ->addFieldToFilter("parent_id", array('eq' => $parentItemId));
        $items = $collection->getItems();
        foreach ($items as $item) {
            $itemIdNew = $this->addDuplicate($item->getData(), $parentItemIdNew, $newGroupId);
            $this->findDuplicate($item->getId(), $groupId, $itemIdNew, $newGroupId);
        }
    }

    private function addDuplicate($data, $parentNewId, $newGroupId) {
        unset($data['item_id']);
        unset($data['creation_time']);
        unset($data['update_time']);
        $data['group_id'] = $newGroupId;
        $data['parent_id'] = $parentNewId;
        $item = $this->_itemFactory->create()->setData($data)->save();

        return $item->getId();
    }
}
