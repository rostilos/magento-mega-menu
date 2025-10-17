<?php
/**
 * Copyright © 2016 Rostilos. All rights reserved.
 */
declare(strict_types=1);

namespace Rostilos\MegaMenu\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Rostilos\MegaMenu\Model\Resolver\DataProvider\MenuTree as ItemDataProvider;

/**
 * Menu item field resolver, used for GraphQL request processing
 */
class MenuTree implements ResolverInterface
{
    /**
     * @var ItemDataProvider
     */
    private $itemDataProvider;

    /**
     * MenuTree constructor.
     * @param ItemDataProvider $itemDataProvider
     */
    public function __construct(
        ItemDataProvider $itemDataProvider
    ) {
        $this->itemDataProvider = $itemDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (isset($value[$field->getName()])) {
            return $value[$field->getName()];
        }

        //check params
        $menuKey = $this->getMenuKey($args);
        $parentId = isset($args['parentId']) ? $args['parentId'] : 0;

        //get data
        try {
            $menuItems = $this-> itemDataProvider->getMenuItems($menuKey, $parentId);
            if (!empty($menuItems)) {
                return $menuItems;
            } else {
                return null;
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
    }

    /**
     * Get menu key
     *
     * @param array $args
     * @return string
     * @throws GraphQlInputException
     */
    private function getMenuKey(array $args): string
    {
        if (!isset($args['menuKey'])) {
            throw new GraphQlInputException(__('"menuKey" should be specified'));
        }

        return $args['menuKey'];
    }
}


