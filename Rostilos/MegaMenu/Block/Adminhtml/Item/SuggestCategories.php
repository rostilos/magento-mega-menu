<?php
namespace Rostilos\MegaMenu\Block\Adminhtml\Item;

class SuggestCategories extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    /**
     * @param $keyword
     * @param $storeId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getJSONCategories($keyword, $storeId)
    {
        if (is_null($storeId)) {
            $storeId = $this->getRequest()->getParam('store', $this->_getDefaultStoreId());
        }

        //get root category id of this store
        $store = $this->_storeManager->getStore($storeId);
        $rootCategoryId = $store->getRootCategoryId();
        if ($store->getId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {
            $rootCategoryId = $this->_storeManager->getDefaultStoreView()->getRootCategoryId();
        }

        $collection = $this->_categoryFactory->create()->getCollection();

        //get matched collection
        $matchedCollection = clone $collection;
        $escapedKeyword = $this->_resourceHelper->addLikeEscape( $keyword, ['position' => 'any'] );
        $matchedCollection->addAttributeToFilter(
            'name',
            ['like' => $escapedKeyword]
        )->addAttributeToFilter(
            'entity_id',
            ['neq' => \Magento\Catalog\Model\Category::TREE_ROOT_ID]
        )->addAttributeToSelect(
            'path'
        )->setStoreId(
            $storeId
        );
        //only get categories is child of current root category
        $matchedCollection->addFieldToFilter('path', ['like' => '%'.$rootCategoryId . '/%']);
        //get matched category ids
        $matchedIds = [];
        foreach ($matchedCollection as $category) {
            $categoryIds = explode('/', $category->getPath());
            foreach ($categoryIds as $id) {
                $matchedIds[] = $id;
            }
        }

        //filter categories collection by matched ids
        $collection->addAttributeToFilter(
            'entity_id',
            ['in' => $matchedIds]
        )->addAttributeToSelect(
            ['name', 'is_active', 'parent_id']
        )->setStoreId(
            $storeId
        );

        //build categories tree for tree suggest
        $categories = [
            \Magento\Catalog\Model\Category::TREE_ROOT_ID => [
                'id' => \Magento\Catalog\Model\Category::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            if (!isset($categories[$category->getId()])) {
                $categories[$category->getId()] = ['id' => $category->getId(), 'children' => []];
            }
            if (!isset($categories[$category->getParentId()])) {
                $categories[$category->getParentId()] = ['id' => $category->getParentId(), 'children' => []];
            }
            $isRoot = ($category->getParentId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) ? true : false;
            $categories[$category->getId()]['is_root'] = $isRoot;
            $categories[$category->getId()]['is_active'] = $category->getIsActive();
            $label =  ($isRoot) ? $category->getName() . " (".__('Root Category') . ")" : $category->getName();
            $categories[$category->getId()]['label'] = $label;
            $categories[$category->getParentId()]['children'][] = & $categories[$category->getId()];
        }

        return $this->_jsonEncoder->encode($categories[\Magento\Catalog\Model\Category::TREE_ROOT_ID]['children']);
    }

}
