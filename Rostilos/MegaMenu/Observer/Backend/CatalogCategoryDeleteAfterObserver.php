<?php

namespace Rostilos\MegaMenu\Observer\Backend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Rostilos\MegaMenu\Helper\Data;
use Rostilos\MegaMenu\Model\Item;

class CatalogCategoryDeleteAfterObserver implements ObserverInterface
{
    protected Data $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $this->helper->deleteRelatedMenuItems(
            Item::LINK_TYPE_CATEGORY,
            [
                'category_ids' => [$category->getId()]
            ],
            false
        );

        return $this;
    }
}
