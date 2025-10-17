<?php

declare(strict_types=1);

namespace Rostilos\MegaMenu\ViewModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Menu implements ArgumentInterface
{
    /**
     * @param $deviceType
     * @param string $menuKey
     * @return string
     */
    public function getMenuHtml($deviceType, string $menuKey = 'main-menu'): string
    {
        // Use block to generate HTML content
        $block = $this->getBlockInstance();
        $block->setData('menu_key', $menuKey);
        $block->setData('device_type',$deviceType);

        return $block->toHtml();
    }

    /**
     * @return mixed
     */
    protected function getBlockInstance(): mixed
    {
        // Retrieve your custom block instance
        $objectManager = ObjectManager::getInstance();
        return $objectManager->get('Rostilos\MegaMenu\Block\Menu');
    }
}
