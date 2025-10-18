<?php
namespace Rostilos\MegaMenu\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Rostilos\MegaMenu\Api\ItemRepositoryInterface;
use Rostilos\MegaMenu\Model\ResourceModel\Item as ResourceItem;
use Rostilos\MegaMenu\Api\Data;
use Rostilos\MegaMenu\Model\ItemFactory;
use Rostilos\MegaMenu\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;

class ItemRepository implements ItemRepositoryInterface
{
    /**
     * @var ResourceItem
     */
    protected $resource;

    /**
     * @var ItemFactory
     */
    protected $itemFactory;

    /**
     * @var ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var Data\ItemSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Rostilos\MegaMenu\Api\Data\ItemInterfaceFactory
     */
    protected $dataGroupFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceItem $resource
     * @param ItemFactory $itemFactory
     * @param Data\ItemInterfaceFactory $dataItemFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param Data\ItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceItem $resource,
        ItemFactory $itemFactory,
        Data\ItemInterfaceFactory $dataItemFactory,
        ItemCollectionFactory $itemCollectionFactory,
        Data\ItemSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->itemFactory = $itemFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataItemFactory = $dataItemFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Item data
     *
     * @param \Rostilos\MegaMenu\Api\Data\ItemInterface $item
     * @return Item
     * @throws CouldNotSaveException
     */
    public function save(\Rostilos\MegaMenu\Api\Data\ItemInterface $item)
    {
        try {
            $this->resource->save($item);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $item;
    }

    /**
     * Load Item data by given Item Identity
     *
     * @param string $itemId
     * @return Item
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($itemId)
    {
        $item = $this->itemFactory->create();
        $item->load($itemId);
        if (!$item->getId()) {
            throw new NoSuchEntityException(__('Menu Item with id "%1" does not exist.', $itemId));
        }

        return $item;
    }

    /**
     * Load Item data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Rostilos\MegaMenu\Model\ResourceModel\Item\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->itemCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $items = [];
        /** @var Item $itemModel */
        foreach ($collection as $itemModel) {
            $itemData = $this->dataItemFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $itemData,
                $itemModel->getData(),
                'Rostilos\MegaMenu\Api\Data\ItemInterface'
            );
            $items[] = $this->dataObjectProcessor->buildOutputDataArray(
                $itemData,
                'Rostilos\MegaMenu\Api\Data\ItermInterface'
            );
        }
        $searchResults->setItems($items);

        return $searchResults;
    }

    /**
     * Delete Item
     *
     * @param \Rostilos\MegaMenu\Api\Data\ItemInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Rostilos\MegaMenu\Api\Data\ItemInterface $item)
    {
        try {
            $this->resource->delete($item);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Item by given Item Identity
     *
     * @param string $itemId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($itemId)
    {
        return $this->delete($this->getById($itemId));
    }
}
