<?php
namespace Rostilos\MegaMenu\Block\Adminhtml\Category;

use Magento\Catalog\Model\Category;
use Magento\Store\Model\Store;
use Rostilos\MegaMenu\Block\Adminhtml\Category\Collection;

class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    public function getSuggestedCategoriesJsonByStore($namePart, $storeId)
    {
        if (is_null($storeId)) {
            $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        }

        $store = $this->_storeManager->getStore($storeId);
        $rootCategoryId = $store->getRootCategoryId();
        if ($store->getId() == Store::DEFAULT_STORE_ID) {
            $rootCategoryId = $this->_storeManager->getDefaultStoreView()->getRootCategoryId();
        }

        /* @var $collection Collection */
        $collection = $this->_categoryFactory->create()->getCollection();

        $matchingNamesCollection = clone $collection;
        $escapedNamePart = $this->_resourceHelper->addLikeEscape(
            $namePart,
            ['position' => 'any']
        );
        $matchingNamesCollection->addAttributeToFilter(
            'name',
            ['like' => $escapedNamePart]
        )->addAttributeToFilter(
            'entity_id',
            ['neq' => Category::TREE_ROOT_ID]
        )->addAttributeToSelect(
            'path'
        )->setStoreId(
            $storeId
        );
        $matchingNamesCollection->addFieldToFilter('path', ['like' => '%'.$rootCategoryId . '/%']);

        $shownCategoriesIds = [];
        foreach ($matchingNamesCollection as $category) {
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }

        $collection->addAttributeToFilter(
            'entity_id',
            ['in' => array_keys($shownCategoriesIds)]
        )->addAttributeToSelect(
            ['name', 'is_active', 'parent_id']
        )->setStoreId(
            $storeId
        );

        $categoryById = [
            Category::TREE_ROOT_ID => [
                'id' => Category::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $isRoot = ($category->getParentId() == Category::TREE_ROOT_ID) ? true : false;
            $categoryById[$category->getId()]['is_root'] = $isRoot;
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $label =  ($isRoot) ? $category->getName() . " (".__('Root Category') . ")" : $category->getName();
            $categoryById[$category->getId()]['label'] = $label;
            $categoryById[$category->getParentId()]['children'][] = & $categoryById[$category->getId()];
        }

        return $this->_jsonEncoder->encode($categoryById[Category::TREE_ROOT_ID]['children']);
    }
}
