<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Rostilos\MegaMenu\Api\Data\GroupInterface;

/**
 * UB Mega Menu Group Model
 *
 * @method \Rostilos\MegaMenu\Model\ResourceModel\Group _getResource()
 * @method \Rostilos\MegaMenu\Model\ResourceModel\Group getResource()
 */
class Group extends \Magento\Framework\Model\AbstractModel implements GroupInterface, IdentityInterface
{
    /**#@+
     * Menu Group's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const TYPE_HORIZONTAL = 'horizontal';
    const TYPE_VERTICAL = 'vertical';
    const TYPE_ACCORDION = 'accordion';
    const TYPE_DRILLDOWN = 'drilldown';
    const TYPE_FOOTER_MENU = 'footer_menu';

    const POSITION_MAIN = 'main';
    const POSITION_SIDEBAR = 'sidebar';
    const POSITION_FOOTER = 'footer';
    const POSITION_OTHER = 'other';

    const OFF_CANVAS_BREAKPOINT_PATH = 'rsmegamenu/general/offcanvas_breakpoint';

    /**#@-*/

    /**
     * UB Mega Menu Group cache tag
     */
    const CACHE_TAG = 'rsmegamenu_group';

    /**
     * @var string
     */
    protected $_cacheTag = 'rsmegamenu_group';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'rsmegamenu_group';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Rostilos\MegaMenu\Model\ResourceModel\Group');
    }

    /**
     * Get customer groups ids
     *
     * @return int[]
     */
    public function getCustomerGroups()
    {
        return $this->hasData('customer_groups') ? $this->getData('customer_groups') : [];
    }

    /**
     * Receive menu group store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Check if menu group identifier exist for specific store
     * return menu group id if menu group exists
     * @param $identifier
     * @param $storeId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }

    /**
     * Prepare menu group's statuses.
     * Available event rsmegamenu_group_get_available_statuses to customize statuses.
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
        return parent::getData(self::GROUP_ID);
    }

    /**
     * Get menu position
     *
     * @return string
     */
    public function getMenuPosition()
    {
        return $this->getData(self::MENU_POSITION);
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Get animation_type
     *
     * @return string
     */
    public function getAnimationType()
    {
        return $this->getData(self::ANIMATION_TYPE);
    }

    /**
     * Get menu type
     *
     * @return string
     */
    public function getMenuType()
    {
        return $this->getData(self::MENU_TYPE);
    }

    /**
     * Get mobile_type
     *
     * @return string
     */
    public function getMobileType()
    {
        return $this->getData(self::MOBILE_TYPE);
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
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setId($id)
    {
        return $this->setData(self::GROUP_ID, $id);
    }

    /**
     * Set menu position
     *
     * @param string $menuPosition
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setMenuPosition($menuPosition)
    {
        return $this->setData(self::MENU_POSITION, $menuPosition);
    }

    /**
     * Set identifier
     *
     * @param string $identifier
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setIdentifier($identifier)
    {
        return $this->setData(self::IDENTIFIER, $identifier);
    }

    /**
     * Set title
     *
     * @param string $title
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Set animation_type
     *
     * @param string $animationType
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setAnimationType($animationType)
    {
        return $this->setData(self::TITLE, $animationType);
    }

    /**
     * Set menu type
     *
     * @param string $menuType
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setMenuType($menuType)
    {
        return $this->setData(self::MENU_TYPE, $menuType);
    }

    /**
     * Set mobile type
     *
     * @param string $mobileType
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setMobileType($mobileType)
    {
        return $this->setData(self::MOBILE_TYPE, $mobileType);
    }

    /**
     * Set description
     *
     * @param string $description
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Set creation time
     *
     * @param string $creationTime
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setCreationTime($creationTime)
    {
        return $this->setData(self::CREATION_TIME, $creationTime);
    }

    /**
     * Set update time
     *
     * @param string $updateTime
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setUpdateTime($updateTime)
    {
        return $this->setData(self::UPDATE_TIME, $updateTime);
    }

    /**
     * Set sort order
     *
     * @param string $sortOrder
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * Set is active
     *
     * @param int|bool $isActive
     * @return \Rostilos\MegaMenu\Api\Data\GroupInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    public function getOptions()
    {
        $rs = [];
        $options = $this->_getResource()->getOptions();
        if ($options) {
            foreach ($options as $option) {
                $rs[$option['group_id']] = $option['title'];
            }
        }

        return $rs;
    }

    public function getGroupIdByIdentifier($identifier, $storeId = null)
    {
        $rsModel = $this->_getResource();
        if ($storeId) {
            $rsModel->setStore($storeId);
        }
        $id = $rsModel->getGroupIdByIdentifier($identifier);
        return $id;
    }

    public function getItems($groupId = null)
    {
        $id = ($groupId) ? $groupId : $this->getId();
        return $this->_getResource()->getItems($id);
    }

    public function getAnimationTypeOptions()
    {
        return [
            'none' => __('None'),
            'venitian' =>  __('Venitian'),
            'pop' => __('Pop'),
            'linear' => __('Linear'),
            'fadeinup' => __('Fade In Up'),
            'fadeindown' => __('Fade In Down'),
            'slideinup' => __('Slide In Up')
        ];
    }

    public function getMenuPositions()
    {
        $options = [];
        if ($this->getOffCanvasBreakpoint() === 'mobile') {
            $options[self::POSITION_MAIN] = __('Main menu (Off-canvas on Mobile)');
        } else {
            $options[self::POSITION_MAIN] = __('Main menu (Off-canvas on all devices)');
        }
        $options[self::POSITION_SIDEBAR] = __('Sidebar');
        $options[self::POSITION_FOOTER] = __('Footer');
        $options[self::POSITION_OTHER] = __('Other');

        return $options;
    }

    public function getMobileTypeOptions()
    {
        return [
            self::TYPE_ACCORDION =>  __('Accordion'),
            self::TYPE_DRILLDOWN => __('Drilldown')
        ];
    }

    public function getAllMenuTypeOptions()
    {
        return [
            self::TYPE_ACCORDION =>  __('Accordion'),
            self::TYPE_DRILLDOWN => __('Drilldown'),
            self::TYPE_HORIZONTAL => __('Horizontal'),
            self::TYPE_VERTICAL => __('Vertical'),
            self::TYPE_FOOTER_MENU => __('Footer Menu'),
        ];
    }

    public function getMenuTypeOptions()
    {
        /** @var \Magento\Framework\Json\EncoderInterface $jsonEncoder */
        $jsonEncoder =  \Magento\Framework\App\ObjectManager::getInstance()
            ->create('\Magento\Framework\Json\EncoderInterface');

        if ($this->getOffCanvasBreakpoint() === 'mobile') {
            $mainMenuTypes = [
                ['key' => self::TYPE_HORIZONTAL, 'value' => __('Horizontal')]
            ];
        } else {
            $mainMenuTypes = [
                ['key' => self::TYPE_ACCORDION, 'value' => __('Accordion')],
                ['key' => self::TYPE_DRILLDOWN, 'value' => __('Drilldown')],
                ['key' => self::TYPE_VERTICAL, 'value' => __('Vertical')]
            ];
        }

        $menuTypes = [
            'main' => $mainMenuTypes,
            'sidebar' => [
                ['key' => self::TYPE_ACCORDION, 'value' => __('Accordion')],
                ['key' => self::TYPE_DRILLDOWN, 'value' => __('Drilldown')],
                ['key' => self::TYPE_VERTICAL, 'value' => __('Vertical')]
            ],
            'footer' => [
                ['key' => self::TYPE_FOOTER_MENU, 'value' => __('Footer Menu')]
            ],
            'other' => [
                ['key' => self::TYPE_ACCORDION, 'value' => __('Accordion')],
                ['key' => self::TYPE_DRILLDOWN, 'value' => __('Drilldown')],
                ['key' => self::TYPE_VERTICAL, 'value' => __('Vertical')],
                ['key' => self::TYPE_HORIZONTAL, 'value' => __('Horizontal')],
                ['key' => self::TYPE_FOOTER_MENU, 'value' => __('Footer Menu')]
            ]
        ];

        return $jsonEncoder->encode($menuTypes);
    }

    public function getOffCanvasBreakpoint() {
        $rs = null;
        $stores = $this->getStores();
        $storeId = isset($stores[0]) ? $stores[0] : null;
        /** @var \Rostilos\Base\Helper\Data $baseHelper */
        $baseHelper =  \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Rostilos\Base\Helper\Data');
        $rs = $baseHelper->getConfigValue(self::OFF_CANVAS_BREAKPOINT_PATH, $storeId);

        return $rs;
    }
}
