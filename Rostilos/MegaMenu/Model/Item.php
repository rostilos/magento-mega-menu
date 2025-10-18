<?php
namespace Rostilos\MegaMenu\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Rostilos\MegaMenu\Api\Data\ItemInterface;

class Item extends \Magento\Framework\Model\AbstractModel implements ItemInterface, IdentityInterface
{
    /**#@+
     * Item's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    const SHOW_TITLE_YES = 1;
    const SHOW_TITLE_NO = 0;

    const IS_GROUP_YES = 1;
    const IS_GROUP_NO = 0;

    const IS_SHOW_THUMB_YES = 1;
    const IS_SHOW_THUMB_NO = 0;

    const LINK_TYPE_CATEGORY = 'category-page';
    const LINK_TYPE_CMS = 'cms-page';
    const LINK_TYPE_CUSTOM = 'custom-link';
    const LINK_TYPE_NO_LINK = 'no-link';

    const SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG = 0;
    const SHOW_NUMBER_PRODUCT_YES = 1;
    const SHOW_NUMBER_PRODUCT_NO = 2;

    const SUB_CONTENT_TYPE_CHILD_ITEMS = 'child-items';
    const SUB_CONTENT_TYPE_CUSTOM_CONTENT = 'custom-content';
    const SUB_CONTENT_TYPE_STATIC_BLOCK = 'static-block';

    const VISIBLE_OPTION_USE_GENERAL_CONFIG = 0;
    const VISIBLE_OPTION_CUSTOM_CONFIG = 1;

    const VISIBLE_IN_DESKTOP = 'desktop';
    const VISIBLE_IN_TABLET = 'tablet';
    const VISIBLE_IN_MOBILE = 'mobile';

    const BASE_WIDTH_PIXEL_TYPE = 1; //pixel
    const BASE_WIDTH_PERCENT_TYPE = 2; //percent

    /**
     * cache tag
     */
    const CACHE_TAG = 'rsmegamenu_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'rsmegamenu_item';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rsmegamenu_item';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Rostilos\MegaMenu\Model\ResourceModel\Item');
    }

    /**
     * Prepare item's statuses.
     * Available event rsmegamenu_item_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ITEM_ID);
    }

    /**
     * Get parent id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * Get group id
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get SEO title
     *
     * @return string
     */
    public function getSEOTitle()
    {
        return $this->getData(self::SEO_TITLE);
    }

    /**
     * Get identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->getData(self::IDENTIFIER);
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->getData(self::LEVEL);
    }

    /**
     * Get Show Title
     *
     * @return bool|null
     */
    public function isShowTitle()
    {
        return (bool)$this->getData(self::SHOW_TITLE);
    }

    /**
     * Get Icon Image
     *
     * @return string|null
     */
    public function getIconImage()
    {
        return $this->getData(self::ICON_IMAGE);
    }

    /**
     * Get Font Awesome
     *
     * @return string|null
     */
    public function getFontAwesome()
    {
        return $this->getData(self::FONT_AWESOME);
    }

    /**
     * Get Link Type
     *
     * @return string|null
     */
    public function getLinkType()
    {
        return $this->getData(self::LINK_TYPE);
    }

    /**
     * Get Link
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->getData(self::LINK);
    }

    /**
     * Get Link Target
     *
     * @return string|null
     */
    public function getLinkTarget()
    {
        return $this->getData(self::LINK_TARGET);
    }

    /**
     * Get Category ID
     *
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * Get Show Number Product
     *
     * @return bool|null
     */
    public function isShowNumberProduct()
    {
        return $this->getData(self::SHOW_NUMBER_PRODUCT);
    }

    /**
     * Get CMS Page
     *
     * @return string|null
     */
    public function getCmsPage()
    {
        return $this->getData(self::CMS_PAGE);
    }

    /**
     * Get Is Group
     *
     * @return bool|null
     */
    public function isGroup()
    {
        return (bool)$this->getData(self::IS_GROUP);
    }

    /**
     * Get Mega Cols
     *
     * @return int|null
     */
    public function getMegaCols()
    {
        return $this->getData(self::MEGA_COLS);
    }

    /**
     * Get Mega Width
     *
     * @return int|null
     */
    public function getMegaWidth()
    {
        return $this->getData(self::MEGA_WIDTH);
    }

    /**
     * Get Mega Base Width Type
     *
     * @return int
     */
    public function getMegaBaseWidthType()
    {
        return $this->getData(self::MEGA_BASE_WIDTH_TYPE);
    }

    /**
     * Get Mega Col Width
     *
     * @return int|null
     */
    public function getMegaColWidth()
    {
        return $this->getData(self::MEGA_COL_WIDTH);
    }

    /**
     * Get Mega ColX Width
     *
     * @return string|null
     */
    public function getMegaColXWidth()
    {
        return $this->getData(self::MEGA_COL_X_WIDTH);
    }

    /**
     * Get Mega Sub Content Type
     *
     * @return string|null
     */
    public function getMegaSubContentType()
    {
        return $this->getData(self::MEGA_SUB_CONTENT_TYPE);
    }

    /**
     * Get Custom Content
     *
     * @return string|null
     */
    public function getCustomContent()
    {
        return $this->getData(self::CUSTOM_CONTENT);
    }

    /**
     * Get Static Blocks
     *
     * @return string|null
     */
    public function getStaticBlocks()
    {
        return $this->getData(self::STATIC_BLOCKS);
    }


    /**
     * Get Visible Option
     *
     * @return bool|null
     */
    public function getVisibleOption()
    {
        return $this->getData(self::VISIBLE_OPTION);
    }

    /**
     * Get Visible In
     *
     * @return string|null
     */
    public function getVisibleIn()
    {
        return $this->getData(self::VISIBLE_IN);
    }

    /**
     * Get Addition Class
     *
     * @return string|null
     */
    public function getAdditionClass()
    {
        return $this->getData(self::ADDITION_CLASS);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Get sort order
     *
     * @return string
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * Is active
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getData(self::IS_ACTIVE);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * Set parent id
     *
     * @param string $parentId
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * Set group id
     *
     * @param string $groupId
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setGroupId($groupId)
    {
        return $this->setData(self::GROUP_ID, $groupId);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set SEO title
     *
     * @param string $title
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setSEOTitle($title)
    {
        return $this->setData(self::SEO_TITLE, $title);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set path
     *
     * @param string $path
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setPath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * Set level
     *
     * @param string $level
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setLevel($level)
    {
        return $this->setData(self::LEVEL, $level);
    }

    /**
     * Set Show Title
     *
     * @param int $isShowTitle
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setIsShowTitle($isShowTitle)
    {
        return $this->setData(self::SHOW_TITLE, $isShowTitle);
    }

    /**
     * Set Icon Image
     *
     * @param string $iconImage
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setIconImage($iconImage)
    {
        return $this->setData(self::ICON_IMAGE, $iconImage);
    }

    /**
     * Set Font Awesome
     *
     * @param string $fontAwesome
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setFontAwesome($fontAwesome)
    {
        return $this->setData(self::FONT_AWESOME, $fontAwesome);
    }

    /**
     * Set Link Type
     *
     * @param string $linkType
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setLinkType($linkType)
    {
        return $this->setData(self::LINK_TYPE, $linkType);
    }

    /**
     * Set Link
     *
     * @param string $link
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setLink($link)
    {
        return $this->setData(self::LINK, $link);
    }

    /**
     * Set Link Target
     *
     * @param string $linkTarget
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setLinkTarget($linkTarget)
    {
        return $this->setData(self::LINK_TARGET, $linkTarget);
    }

    /**
     * Set Category ID
     *
     * @param string $categoryId
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::CATEGORY_ID, $categoryId);
    }

    /**
     * Set Show Number Product
     *
     * @param int $isShowNumberProduct
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setIsShowNumberProduct($isShowNumberProduct)
    {
        return $this->setData(self::SHOW_NUMBER_PRODUCT, $isShowNumberProduct);
    }

    /**
     * Set CMS page
     *
     * @param string $cmsPage
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setCmsPage($cmsPage)
    {
        return $this->setData(self::CMS_PAGE, $cmsPage);
    }

    /**
     * Set Is Group
     *
     * @param string $isGroup
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setIsGroup($isGroup)
    {
        return $this->setData(self::IS_GROUP, $isGroup);
    }

    /**
     * Set Mega Cols
     *
     * @param string $megaCols
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setMegaCols($megaCols)
    {
        return $this->setData(self::MEGA_COLS, $megaCols);
    }

    /**
     * Set Mega Width
     *
     * @param string $megaWidth
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setMegaWidth($megaWidth)
    {
        return $this->setData(self::MEGA_WIDTH, $megaWidth);
    }

    /**
     * Set Mega Base Width Type
     *
     * @param string $baseWidthType
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setMegaBaseWidthType($baseWidthType)
    {
        return $this->setData(self::MEGA_BASE_WIDTH_TYPE, $baseWidthType);
    }

    /**
     * Set Mega Col Width
     *
     * @param string $megaColWidth
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setMegaColWidth($megaColWidth)
    {
        return $this->setData(self::MEGA_COL_WIDTH, $megaColWidth);
    }

    /**
     * Set Mega Col X Width
     *
     * @param string $megaColXWidth
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setMegaColXWidth($megaColXWidth)
    {
        return $this->setData(self::MEGA_COL_X_WIDTH, $megaColXWidth);
    }

    /**
     * Set Mega Sub Content Type
     *
     * @param string $megaSubContentType
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setMegaSubContentType($megaSubContentType)
    {
        return $this->setData(self::MEGA_SUB_CONTENT_TYPE, $megaSubContentType);
    }

    /**
     * Set Custom Content
     *
     * @param string $customContent
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setCustomContent($customContent)
    {
        return $this->setData(self::TITLE, $customContent);
    }

    /**
     * Set Static Blocks
     *
     * @param string $staticBlocks
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setStaticBlocks($staticBlocks)
    {
        return $this->setData(self::STATIC_BLOCKS, $staticBlocks);
    }

    /**
     * Set Visible Option
     *
     * @param bool $visibleOption
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setVisibleOption($visibleOption)
    {
        return $this->setData(self::VISIBLE_OPTION, $visibleOption);
    }

    /**
     * Set Visible In
     *
     * @param string $visibleIn
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setVisibleIn($visibleIn)
    {
        return $this->setData(self::VISIBLE_IN, $visibleIn);
    }

    /**
     * Set Addition Class
     *
     * @param string $additionClass
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setAdditionClass($additionClass)
    {
        return $this->setData(self::ADDITION_CLASS, $additionClass);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Rostilos\MegaMenu\Api\Data\ItemInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function getMenuGroupOptions()
    {
        $rs = [];
        $options = $this->_getResource()->getMenuGroupOptions();
        if ($options) {
            foreach ($options as $option) {
                $rs[$option['group_id']] = $option['title'];
            }
        }

        return $rs;
    }

    public function getMenuItemOptions($groupId = null)
    {
        $groupId = $groupId ? $groupId : $this->getGroupId();

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Rostilos\MegaMenu\Helper\Data $helper */
        $helper = $om->get('\Rostilos\MegaMenu\Helper\Data');

        //load menu item collection
        $collection = $this->getCollection()
            ->addFieldToSelect(['item_id', 'parent_id', 'title', 'level'])
            ->addFieldToFilter('group_id', ['in' => [$groupId]])
            ->addOrder('sort_order', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->addOrder('title', \Magento\Framework\Data\Collection::SORT_ORDER_ASC)
            ->load();

        //build tree options
        $options = $helper->buildTree(
            0,
            $collection,
            99,
            'title',
            'item_id',
            'parent_id',
            true
        );

        //rename blank item
        $options[0] = __('-- None --');

        return $options;
    }

    public function getLinkTargetOptions()
    {
        return [
            '_self' => __('Self'),
            '_blank' => __('Blank'),
            '_top' => __('Top'),
            '_parent' => __('Parent'),
        ];
    }

    public function getShowTitleOptions()
    {
        return [
            self::SHOW_TITLE_YES => __('Yes'),
            self::SHOW_TITLE_NO => __('No')
        ];
    }

    public function getShowNumberProductOptions()
    {
        return [
            self::SHOW_NUMBER_PRODUCT_YES => __('Yes'),
            self::SHOW_NUMBER_PRODUCT_NO => __('No'),
            self::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG => __('Use General Config'),
        ];
    }

    public function getLinkTypeOptions()
    {
        return [
            self::LINK_TYPE_CATEGORY => __('Category Page'),
            self::LINK_TYPE_CMS => __('CMS Page'),
            self::LINK_TYPE_CUSTOM => __('Custom Link'),
            self::LINK_TYPE_NO_LINK => __('Disable Link'),
        ];
    }

    public function getIsGroupOptions()
    {
        return [
            self::IS_GROUP_YES => __('Yes'),
            self::IS_GROUP_NO => __('No')
        ];
    }

    public function getSubMenuContentOptions()
    {
        return [
            self::SUB_CONTENT_TYPE_CHILD_ITEMS => __('Child menu items'),
            self::SUB_CONTENT_TYPE_STATIC_BLOCK => __('Static Blocks'),
            self::SUB_CONTENT_TYPE_CUSTOM_CONTENT => __('Custom Content')
        ];
    }

    public function getIsShowThumb()
    {
        return [
            self::IS_SHOW_THUMB_YES => __('Yes'),
            self::IS_SHOW_THUMB_NO => __('No')
        ];
    }

    public function getCategoryOptions()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        //get menu group
        $menuGroup = $om->create('Rostilos\MegaMenu\Model\Group');
        $menuGroup->load($this->getGroupId());
        //get store ids of menu group
        $stores = $menuGroup->getStores();
        $storeId = isset($stores[0]) ? $stores[0] : null;
        //get categories options by store
        $helper = $om->get('\Rostilos\MegaMenu\Helper\Data');
        $options = $helper->getCategoryOptions($storeId, false, true);

        return $options;
    }

    public function getCMSPageOptions()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        //get menu group
        $menuGroup = $om->create('Rostilos\MegaMenu\Model\Group');
        $menuGroup->load($this->getGroupId());
        //get store ids of menu group
        $storeIds = $menuGroup->getStores();

        //get cms page options by store
        $helper = $om->get('\Rostilos\MegaMenu\Helper\Data');
        $options = $helper->getCMSPageOptions($storeIds);

        return $options;
    }

    public function getStaticBlockOptions()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        //get menu group
        $menuGroup = $om->create('Rostilos\MegaMenu\Model\Group');
        $menuGroup->load($this->getGroupId());
        //get store ids of menu group
        $storeIds = $menuGroup->getStores();

        //get static block options by store
        $helper = $om->get('\Rostilos\MegaMenu\Helper\Data');
        $options = $helper->getStaticBlockOptions($storeIds);

        return $options;
    }

    public function getVisibleOptions()
    {
        return [
            self::VISIBLE_OPTION_USE_GENERAL_CONFIG => __('Use General Config'),
            self::VISIBLE_OPTION_CUSTOM_CONFIG => __('Customize Config')
        ];
    }

    public function getVisibleInOptions()
    {
        return [
            self::VISIBLE_IN_DESKTOP => __('Desktop'),
            self::VISIBLE_IN_TABLET => __('Tablet'),
            self::VISIBLE_IN_MOBILE => __('Mobile')
        ];
    }

    public function getLinkTypeText()
    {
        $options = $this->getLinkTypeOptions();
        return $options[$this->getLinkType()];
    }

    public function getBaseWidthTypeOptions()
    {
        return [
            self::BASE_WIDTH_PIXEL_TYPE => __('Fixed Width (px)'),
            self::BASE_WIDTH_PERCENT_TYPE => __('Dynamic Width (%)')
        ];
    }

}
