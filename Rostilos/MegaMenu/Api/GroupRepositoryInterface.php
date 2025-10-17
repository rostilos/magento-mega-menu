<?php
namespace Rostilos\MegaMenu\Api;

interface GroupRepositoryInterface
{
    public function save(\Rostilos\MegaMenu\Api\Data\GroupInterface $group);

    public function getById($groupId);

    public function getByMenuKey($menuKey);

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    public function delete(\Rostilos\MegaMenu\Api\Data\GroupInterface $group);

    public function deleteById($groupId);
}
