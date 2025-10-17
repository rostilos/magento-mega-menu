<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Block\Adminhtml\Category;

use Rostilos\MegaMenu\Block\Adminhtml\Category\Collection;

/**
 * Categories tree block
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    /**
     * Retrieve list of categories with name containing $namePart and their parents
     * @param $namePart
     * @param $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSuggestedCategoriesJsonByStore($namePart, $storeId)
    {
        if (is_null($storeId)) {
            $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        }

        //get root category id of this store
        $store = $this->_storeManager->getStore($storeId);
        $rootCategoryId = $store->getRootCategoryId();
        if ($store->getId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            //maybe coming soon
            /*$defaultStoreItems = $this->_categoryFactory->create()->getCollection()
                ->addFieldToFilter('parent_id', ['in' => [$rootCategoryId]]);
            $rootCategoryId = $defaultStoreItems->getFirstItem()->getId();*/
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
            ['neq' => \Magento\Catalog\Model\Category::TREE_ROOT_ID]
        )->addAttributeToSelect(
            'path'
        )->setStoreId(
            $storeId
        );
        //only get categories is child of current root category
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
            \Magento\Catalog\Model\Category::TREE_ROOT_ID => [
                'id' => \Magento\Catalog\Model\Category::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $isRoot = ($category->getParentId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) ? true : false;
            $categoryById[$category->getId()]['is_root'] = $isRoot;
            $categoryById[$category->getId()]['is_active'] = $category->getIsActive();
            $label =  ($isRoot) ? $category->getName() . " (".__('Root Category') . ")" : $category->getName();
            $categoryById[$category->getId()]['label'] = $label;
            $categoryById[$category->getParentId()]['children'][] = & $categoryById[$category->getId()];
        }

        return $this->_jsonEncoder->encode($categoryById[\Magento\Catalog\Model\Category::TREE_ROOT_ID]['children']);
    }
}
