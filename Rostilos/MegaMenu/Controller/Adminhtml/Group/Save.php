<?php
namespace Rostilos\MegaMenu\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Rostilos_MegaMenu::group_save';

    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor
    ) {
        $this->dataProcessor = $dataProcessor;
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
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            //we use this if need to pre-process data before save
            //$data = $this->dataProcessor->filter($data);

            $model = $this->_objectManager->create('Rostilos\MegaMenu\Model\Group');

            $id = $this->getRequest()->getParam('group_id');
            if ($id) {
                $model->load($id);
            }

            if (isset($data['customer_groups'])) {
                $data['customer_group_id'] = $data['customer_groups'];
            }
            if (isset($data['stores'])) {
                $data['store_id'] = $data['stores'];
            }

            //save current posted form's data to session
            $this->_getSession()->setFormData($data);

            //set new data
            $model->setData($data);

            /*$this->_eventManager->dispatch(
                'rsmegamenu_group_prepare_save',
                ['group' => $model, 'request' => $this->getRequest()]
            );*/

            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $model->getId(), '_current' => true]);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this Menu Group.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['group_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['group_id' => $model->getId(), '_current' => true]);
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while saving the menu group information') .": ". $e->getMessage());
            }

            return $resultRedirect->setPath(
                '*/*/edit',
                ['group_id' => $this->getRequest()->getParam('group_id')]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
