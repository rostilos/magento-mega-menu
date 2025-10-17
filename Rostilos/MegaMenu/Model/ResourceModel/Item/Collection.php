<?php
namespace Rostilos\MegaMenu\Model\ResourceModel\Item;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{

    protected $_idFieldName = 'item_id';

    protected StoreManagerInterface $storeManager;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    protected function _construct()
    {
        $this->_init(
            'Rostilos\MegaMenu\Model\Item',
            'Rostilos\MegaMenu\Model\ResourceModel\Item'
        );
        $this->_map['fields']['item_id'] = 'main_table.item_id';
    }

    public function toOptionIdArray()
    {
        $res = [];
        $existingIds = [];
        foreach ($this as $item) {
            $id = $item->getData('item_id');

            $data['value'] = $id;
            $data['label'] = $item->getData('title');

            if (in_array($id, $existingIds)) {
                $data['value'] .= '|' . $item->getData('item_id');
            } else {
                $existingIds[] = $id;
            }

            $res[] = $data;
        }

        return $res;
    }

    public function getSelectCountSql()
    {
        /** @var ObjectManagerInterface $om */
        $om = ObjectManager::getInstance();
        $groupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        if ($groupId) {
            $this->addFieldToFilter('group_id', $groupId);
        }

        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);

        return $countSelect;
    }

    protected function _beforeLoad()
    {
        /** @var ObjectManagerInterface $om */
        $om = ObjectManager::getInstance();
        $groupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        if ($groupId) {
            $this->addFieldToFilter('group_id', $groupId);
        }

        return parent::_beforeLoad();
    }

    /**
     * Join menu group relation table if there is menu group filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        /** @var ObjectManagerInterface $om */
        $om = ObjectManager::getInstance();
        $groupId = $om->get('Magento\Backend\Model\Session')->getMenuGroupId();
        if ($groupId) {
            $this->addFieldToFilter('group_id', $groupId);
        }

        parent::_renderFiltersBefore();
    }
}
