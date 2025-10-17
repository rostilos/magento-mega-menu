<?php
namespace Rostilos\MegaMenu\Api\Data;

interface GroupInterface
{
    const GROUP_ID                 = 'group_id';
    const MENU_POSITION            = 'menu_position';
    const TITLE                    = 'title';
    const IDENTIFIER               = 'identifier';
    const ANIMATION_TYPE           = 'animation_type';
    const MENU_TYPE                = 'menu_type';
    const MOBILE_TYPE              = 'mobile_type';
    const DESCRIPTION              = 'description';
    const CREATION_TIME            = 'creation_time';
    const UPDATE_TIME              = 'update_time';
    const IS_ACTIVE                = 'is_active';
    const SORT_ORDER               = 'sort_order';

    public function getId();

    public function getMenuPosition();

    public function getTitle();

    public function getIdentifier();

    public function getAnimationType();

    public function getMenuType();

    public function getMobileType();

    public function getDescription();

    public function getCreationTime();

    public function getUpdateTime();

    public function getSortOrder();

    public function isActive();

    public function setId($id);

    public function setMenuPosition($menuPosition);

    public function setTitle($title);

    public function setIdentifier($identifier);

    public function setAnimationType($animationType);

    public function setMenuType($menuType);

    public function setMobileType($mobileType);

    public function setDescription($description);

    public function setCreationTime($creationTime);

    public function setUpdateTime($updateTime);

    public function setSortOrder($sortOrder);

    public function setIsActive($isActive);
}
