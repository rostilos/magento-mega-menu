<?php
namespace Rostilos\MegaMenu\Api;

interface ItemRepositoryInterface
{
    public function save(\Rostilos\MegaMenu\Api\Data\ItemInterface $item);

    public function getById($itemId);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(\Rostilos\MegaMenu\Api\Data\ItemInterface $item);

    public function deleteById($itemId);
}
