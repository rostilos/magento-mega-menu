<?php

namespace Rostilos\MegaMenu\Observer\Backend;

use Magento\Framework\Event\ObserverInterface;

class CmsPageDeleteAfterObserver implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $baseHelper = $om->get('Rostilos\Base\Helper\Data');
        $helperData = $om->get('Rostilos\MegaMenu\Helper\Data');

        $isAllowed = (bool)$baseHelper->getConfigValueByKey('auto_sync_cmspage_menu_item', ['rsmegamenu']);
        if (!$isAllowed) {
            return;
        }
        $pageId = $helperData->getRequest()->getParam('page_id');
        if ($pageId) {
            $helperData->deleteRelatedMenuItems(
                \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CMS,
                [
                    'cms_page_ids' => [$pageId]
                ],
                false
            );
        }

        return $this;
    }
}
