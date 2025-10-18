<?php
namespace Rostilos\MegaMenu\Controller\Adminhtml\Group;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Rostilos\MegaMenu\Model\Group as GroupModel;
use Rostilos\MegaMenu\Model\ResourceModel\Group\CollectionFactory;

abstract class MassAction extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var string success message
     */
    protected $successMessage = 'Mass action successfully on %1 records';

    /**
     * @var string error message
     */
    protected $errorMessage = 'Mass action failed';

    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @param GroupModel $group
     * @return mixed
     */
    protected abstract function runAction(GroupModel $group);

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $size = $collection->getSize();
            foreach ($collection as $model) {
                $this->runAction($model);
            }
            $this->messageManager->addSuccessMessage(__($this->successMessage, $size));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($this->errorMessage));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
