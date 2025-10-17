<?php

declare(strict_types=1);

namespace Rostilos\MegaMenu\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Rostilos\MegaMenu\Model\Group;
use Rostilos\MegaMenu\Model\GroupFactory;
use Rostilos\MegaMenu\Model\Item;
use Rostilos\MegaMenu\Model\ItemFactory;
use Rostilos\MegaMenu\Api\GroupRepositoryInterface;

class AddInitialMenuData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Group factory
     *
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * Item factory
     *
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GroupFactory $groupFactory
     * @param ItemFactory $itemFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupFactory $groupFactory,
        ItemFactory $itemFactory,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->groupFactory = $groupFactory;
        $this->itemFactory = $itemFactory;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $filter = $this->filterBuilder->setField('identifier')
            ->setValue('main-menu')
            ->setConditionType('eq')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
        $groupResults = $this->groupRepository->getList($searchCriteria)->getItems();

        if (empty($groupResults)) {
            $menuGroupData = [
                'title' => 'Top Menu',
                'identifier' => 'main-menu',
                'menu_position' => Group::POSITION_MAIN,
                'is_active' => Group::STATUS_ENABLED,
                'menu_type' => Group::TYPE_HORIZONTAL,
                'animation_type' => 'none',
                'description' => 'Top Menu Default',
                'sort_order' => 0
            ];

            $group = $this->groupFactory->create()->setData($menuGroupData);
            $group->setStores([0]);
            $group->setCustomerGroups([0]);
            $group->save();

            $groupId = $group->getId();

            $menuItems = [
                [
                    'title' => 'Home',
                    'identifier' => 'home',
                    'path' => '',
                    'level' => 1,
                    'parent_id' => 0,
                    'group_id' => $groupId,
                    'show_title' => Item::SHOW_TITLE_YES,
                    'icon_image' => '',
                    'font_awesome' => 'fa fa-home',
                    'link_type' => Item::LINK_TYPE_CUSTOM,
                    'link' => '#',
                    'link_target' => '_self',
                    'category_id' => null,
                    'show_number_product' => Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG,
                    'cms_page' => null,
                    'is_group' => Item::IS_GROUP_NO,
                    'mega_cols' => 1,
                    'mega_width' => 0,
                    'mega_col_width' => 0,
                    'mega_col_x_width' => null,
                    'mega_sub_content_type' => Item::SUB_CONTENT_TYPE_CHILD_ITEMS,
                    'custom_content' => null,
                    'static_blocks' => null,
                    'addition_class' => null,
                    'description' => null,
                    'is_active' => Group::STATUS_ENABLED,
                    'sort_order' => 0
                ],
                [
                    'title' => 'Products',
                    'identifier' => 'products',
                    'path' => '',
                    'level' => 1,
                    'parent_id' => 0,
                    'group_id' => $groupId,
                    'show_title' => Item::SHOW_TITLE_YES,
                    'icon_image' => '',
                    'font_awesome' => 'fab fa-product-hunt',
                    'link_type' => Item::LINK_TYPE_CUSTOM,
                    'link' => '#',
                    'link_target' => '_self',
                    'category_id' => null,
                    'show_number_product' => Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG,
                    'cms_page' => null,
                    'is_group' => Item::IS_GROUP_NO,
                    'mega_cols' => 1,
                    'mega_width' => 0,
                    'mega_col_width' => 0,
                    'mega_col_x_width' => null,
                    'mega_sub_content_type' => Item::SUB_CONTENT_TYPE_CHILD_ITEMS,
                    'custom_content' => null,
                    'static_blocks' => null,
                    'addition_class' => null,
                    'description' => null,
                    'is_active' => Group::STATUS_ENABLED,
                    'sort_order' => 0
                ],
                [
                    'title' => 'Service',
                    'identifier' => 'service',
                    'path' => '',
                    'level' => 1,
                    'parent_id' => 0,
                    'group_id' => $groupId,
                    'show_title' => Item::SHOW_TITLE_YES,
                    'icon_image' => '',
                    'font_awesome' => 'fab fa-servicestack',
                    'link_type' => Item::LINK_TYPE_CUSTOM,
                    'link' => '#',
                    'link_target' => '_self',
                    'category_id' => null,
                    'show_number_product' => Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG,
                    'cms_page' => null,
                    'is_group' => Item::IS_GROUP_NO,
                    'mega_cols' => 1,
                    'mega_width' => 0,
                    'mega_col_width' => 0,
                    'mega_col_x_width' => null,
                    'mega_sub_content_type' => Item::SUB_CONTENT_TYPE_CHILD_ITEMS,
                    'custom_content' => null,
                    'static_blocks' => null,
                    'addition_class' => null,
                    'description' => null,
                    'is_active' => Group::STATUS_ENABLED,
                    'sort_order' => 0
                ]
            ];

            foreach ($menuItems as $itemData) {
                $this->itemFactory->create()->setData($itemData)->save();
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
