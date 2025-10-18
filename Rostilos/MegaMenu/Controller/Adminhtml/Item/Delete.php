<?php
namespace Rostilos\MegaMenu\Controller\Adminhtml\Item;

class Delete extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Rostilos_MegaMenu::item_delete';

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
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('item_id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Rostilos\MegaMenu\Model\Item');
                $model->load($id);

                //delete item
                $model->delete();

                // display success message
                $this->messageManager->addSuccess(__('The menu item has been deleted.'));

                // go to list menu items
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {

                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['item_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a menu item to delete.'));

        // go to list menu items
        return $resultRedirect->setPath('*/*/');
    }
}
