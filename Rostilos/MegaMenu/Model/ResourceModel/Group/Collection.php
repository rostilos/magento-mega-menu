<?php
namespace Rostilos\MegaMenu\Model\ResourceModel\Group;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
    * @var string
    */
    protected $_idFieldName = 'group_id';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Rostilos\MegaMenu\Model\Group',
            'Rostilos\MegaMenu\Model\ResourceModel\Group'
        );
        $this->_map['fields']['group_id'] = 'main_table.group_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
        $this->_map['fields']['customer_group'] = 'customer_group_table.customer_group_id';
    }

    /**
     * Returns pairs identifier - title for unique identifiers
     * and pairs identifier|group_id - title for non-unique after first
     *
     * @return array
     */
    public function toOptionIdArray()
    {
        $res = [];
        $existingIdentifiers = [];
        foreach ($this as $item) {
            $identifier = $item->getData('identifier');

            $data['value'] = $identifier;
            $data['label'] = $item->getData('title');

            if (in_array($identifier, $existingIdentifiers)) {
                $data['value'] .= '|' . $item->getData('group_id');
            } else {
                $existingIdentifiers[] = $identifier;
            }

            $res[] = $data;
        }

        return $res;
    }

    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        return $countSelect;
    }

    /**
     * Load related stores data after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function loadStores($tableName, $columnName)
    {
        $items = $this->getColumnValues($columnName);
        $tableAlias = $tableName;
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from([$tableAlias => $this->getTable($tableName)])
                ->where($tableAlias . '.' . $columnName . ' IN (?)', $items);
            $result = $connection->fetchPairs($select);
            if ($result) {
                foreach ($this as $item) {
                    $entityId = $item->getData($columnName);
                    if (!isset($result[$entityId])) {
                        continue;
                    }
                    if ($result[$entityId] == 0) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = $result[$item->getData($columnName)];
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', [$result[$entityId]]);
                }
            }
        }
    }

    /**
     * @param $tableName
     * @param $columnName
     */
    protected function loadCustomerGroups($tableName, $columnName)
    {
        $groupIds = $this->getColumnValues('group_id');
        if (count($groupIds)) {
            foreach ($this as $group) {
                $groupId = $group->getData('group_id');
                $connection = $this->getConnection();
                $select = $connection->select()->from(
                    $this->getTable($tableName),
                    $columnName
                )->where(
                    'group_id = :group_id'
                );
                $binds = [':group_id' => (int)$groupId];
                $assignedCusGroupIds = $connection->fetchCol($select, $binds);
                $group->setData('customer_groups', $assignedCusGroupIds);
            }
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }

    /**
     * Add filter by customer group
     *
     * @param int
     * @return $this
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        if (!$this->getFlag('customer_group_filter_added')) {
            $this->addFilter('customer_group', ['in' => $customerGroupId], 'public');
        }
        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $columnName)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = store_table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Join customer group relation table if there is customer group filter
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function joinCustomerGroupRelationTable($tableName, $columnName)
    {
        if ($this->getFilter('customer_group')) {
            $this->getSelect()->join(
                ['customer_group_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = customer_group_table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->loadStores('rsmegamenu_group_store', 'group_id');
        $this->loadCustomerGroups('rsmegamenu_group_customer_group', 'customer_group_id');
        return parent::_afterLoad();
    }

    /**
     * Perform operations before rendering filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('rsmegamenu_group_store', 'group_id');
        $this->joinCustomerGroupRelationTable('rsmegamenu_group_customer_group', 'group_id');
    }
}
