<?php
namespace Rostilos\MegaMenu\Controller\Adminhtml\Group;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Rostilos\MegaMenu\Api\Data\GroupInterface;
use Rostilos\MegaMenu\Api\GroupRepositoryInterface as GroupRepository;

/**
 * Menu Group grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action
{
    /** @var PostDataProcessor */
    protected $dataProcessor;

    /** @var GroupRepository  */
    protected $groupRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param GroupRepository $groupRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        GroupRepository $groupRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->groupRepository = $groupRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        foreach (array_keys($postItems) as $groupId) {
            /** @var \Rostilos\MegaMenu\Model\Group $group */
            $group = $this->groupRepository->getById($groupId);
            try {
                $groupData = $this->filterPost($postItems[$groupId]);
                $this->validatePost($groupData, $group, $error, $messages);
                $extendedGroupData = $group->getData();
                $this->setGroupData($group, $extendedGroupData, $groupData);
                $this->groupRepository->save($group);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithGroupId($group, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithGroupId($group, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithGroupId(
                    $group,
                    __('Something went wrong while saving the Menu Group.')
                );
                $error = true;
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Filtering posted data.
     *
     * @param array $postData
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $groupData = $this->dataProcessor->filter($postData);
        return $groupData;
    }

    /**
     * Validate post data
     *
     * @param array $groupData
     * @param \Rostilos\MegaMenu\Model\Group $group
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(
        array                          $groupData,
        \Rostilos\MegaMenu\Model\Group $group,
                                       &$error,
        array                          &$messages
    ) {
        if (!($this->dataProcessor->validate($groupData) && $this->dataProcessor->validateRequireEntry($groupData))) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithGroupId($group, $error->getText());
            }
        }
    }

    /**
     * Add menu group title to error message
     *
     * @param GroupInterface $group
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithGroupId(GroupInterface $group, $errorText)
    {
        return '[Menu Group ID: ' . $group->getId() . '] ' . $errorText;
    }

    /**
     * Set menu group data
     *
     * @param \Rostilos\MegaMenu\Model\Group $group
     * @param array $extendedGroupData
     * @param array $groupData
     * @return $this
     */
    public function setGroupData(\Rostilos\MegaMenu\Model\Group $group, array $extendedGroupData, array $groupData)
    {
        $group->setData(array_merge($group->getData(), $extendedGroupData, $groupData));
        return $this;
    }
}
