<?php

namespace Rostilos\MegaMenu\Model\Resolver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class MenuBlockResolver implements ResolverInterface
{
    /**
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(
        Field       $field,
                    $context,
        ResolveInfo $info,
        array       $value = null,
        array       $args = null
    ): array
    {
        if (empty($args['menu_key'])) {
            throw new GraphQlInputException(__('You must specify a menu_key'));
        }

        if (empty($args['device_type'])) {
            throw new GraphQlInputException(__('You must specify a device_type'));
        }
        // Retrieve HTML content from block
        $blockHtml = $this->getBlockHtml($args);

        // Return as part of GraphQL response
        return ['html' => $blockHtml];
    }

    /**
     * @param $args
     * @return string
     */
    protected function getBlockHtml($args): string
    {
        // Use block to generate HTML content
        $block = $this->getBlockInstance();
        $block->setData('menu_key', $args['menu_key'][0]);
        $block->setData('device_type', $args['device_type'][0]);

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
