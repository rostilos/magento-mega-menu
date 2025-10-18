<?php
namespace Rostilos\MegaMenu\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Rostilos\MegaMenu\Api\Data\ItemInterface;

interface ItemRepositoryInterface
{
    public function save(ItemInterface $item);

    public function getById($itemId);

    public function getList(SearchCriteriaInterface $searchCriteria);

    public function delete(ItemInterface $item);

    public function deleteById($itemId);
}
