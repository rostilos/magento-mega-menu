<?php
namespace Rostilos\MegaMenu\Api;

use Rostilos\MegaMenu\Api\Data\GroupInterface;

interface GroupRepositoryInterface
{
    public function save(GroupInterface $group);

    public function getById($groupId);

    public function getByMenuKey($menuKey);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(GroupInterface $group);

    public function deleteById($groupId);
}
