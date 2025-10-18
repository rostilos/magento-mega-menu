<?php
namespace Rostilos\MegaMenu\Plugin\Cms\Adminhtml\Page;

class MassDelete extends \Magento\Cms\Controller\Adminhtml\Page\MassDelete
{
    public function beforeExecute(\Magento\Cms\Controller\Adminhtml\Page\MassDelete $subject)
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Rostilos\Base\Helper\Data $baseHelper */
        $baseHelper = $om->get('Rostilos\Base\Helper\Data');
        /** @var \Rostilos\MegaMenu\Helper\Data $helperData */
        $helperData = $om->get('Rostilos\MegaMenu\Helper\Data');

        //check has allowed
        $isAllowed = (bool)$baseHelper->getConfigValueByKey('auto_sync_cmspage_menu_item', ['rsmegamenu']);
        if (!$isAllowed) {
            return [];
        }

        $collection = $subject->filter->getCollection($subject->collectionFactory->create());
        foreach ($collection as $item) {
            $helperData->deleteRelatedMenuItems(
                \Rostilos\MegaMenu\Model\Item::LINK_TYPE_CMS,
                [
                    'cms_page_ids' => [$item->getId()]
                ],
                false
            );
        }

        return [];
    }
}
