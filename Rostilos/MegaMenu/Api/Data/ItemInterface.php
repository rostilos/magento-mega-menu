<?php

namespace Rostilos\MegaMenu\Api\Data;

interface ItemInterface
{
    const ITEM_ID                 = 'item_id';
    const PARENT_ID               = 'parent_id';
    const GROUP_ID                = 'group_id';
    const TITLE                   = 'title';
    const SEO_TITLE               = 'seo_title';
    const IDENTIFIER              = 'identifier';
    const PATH                    = 'path';
    const LEVEL                   = 'level';
    const SHOW_TITLE              = 'show_title';
    const ICON_IMAGE              = 'icon_image';
    const FONT_AWESOME            = 'font_awesome';
    const LINK_TYPE               = 'link_type';
    const LINK                    = 'link';
    const LINK_TARGET             = 'link_target';
    const CATEGORY_ID             = 'category_id';
    const SHOW_NUMBER_PRODUCT     = 'show_number_product';
    const CMS_PAGE                = 'cms_page';
    const IS_GROUP                = 'is_group';
    const MEGA_COLS               = 'mega_cols';
    const MEGA_WIDTH              = 'mega_width';
    const MEGA_BASE_WIDTH_TYPE    = 'mega_base_width_type';
    const MEGA_COL_WIDTH          = 'mega_col_width';
    const MEGA_COL_X_WIDTH        = 'mega_col_x_width';
    const MEGA_SUB_CONTENT_TYPE   = 'mega_sub_content_type';
    const CUSTOM_CONTENT          = 'custom_content';
    const STATIC_BLOCKS           = 'static_blocks';
    const VISIBLE_OPTION          = 'visible_option';
    const VISIBLE_IN              = 'visible_in';
    const ADDITION_CLASS          = 'addition_class';
    const DESCRIPTION             = 'description';
    const CREATION_TIME           = 'creation_time';
    const UPDATE_TIME             = 'update_time';
    const IS_ACTIVE               = 'is_active';
    const SORT_ORDER              = 'sort_order';

    public function getId();

    public function getParentId();

    public function getGroupId();

    public function getTitle();

    public function getSEOTitle();

    public function getIdentifier();

    public function getPath();

    public function getLevel();

    public function isShowTitle();

    public function getIconImage();

    public function getFontAwesome();

    public function getLinkType();

    public function getLink();

    public function getLinkTarget();

    public function getCategoryId();

    public function isShowNumberProduct();

    public function getCmsPage();

    public function isGroup();

    public function getMegaCols();

    public function getMegaWidth();

    public function getMegaBaseWidthType();

    public function getMegaColWidth();

    public function getMegaColXWidth();

    public function getMegaSubContentType();

    public function getCustomContent();

    public function getStaticBlocks();

    public function getVisibleOption();

    public function getVisibleIn();

    public function getAdditionClass();

    public function getDescription();

    public function getCreationTime();

    public function getUpdateTime();

    public function getSortOrder();

    public function isActive();

    public function setId($id);

    public function setParentId($parentId);

    public function setGroupId($groupId);

    public function setTitle($title);

    public function setSEOTitle($title);

    public function setIdentifier($identifier);

    public function setPath($path);

    public function setLevel($level);

    public function setIsShowTitle($isShowTitle);

    public function setIconImage($iconImage);

    public function setFontAwesome($fontAwesome);

    public function setLinkType($linkType);

    public function setLink($link);

    public function setLinkTarget($linkTarget);

    public function setCategoryId($categoryId);

    public function setIsShowNumberProduct($isShowNumberProduct);

    public function setCmsPage($cmsPage);

    public function setIsGroup($isGroup);

    public function setMegaCols($megaCols);

    public function setMegaWidth($megaWidth);

    public function setMegaBaseWidthType($baseWidthType);

    public function setMegaColWidth($megaColWidth);

    public function setMegaColXWidth($megaColXWidth);

    public function setMegaSubContentType($megaSubContentType);

    public function setCustomContent($customContent);

    public function setStaticBlocks($staticBlocks);

    public function setVisibleOption($visibleOption);

    public function setVisibleIn($visibleIn);

    public function setAdditionClass($additionClass);

    public function setDescription($description);

    public function setCreationTime($creationTime);

    public function setUpdateTime($updateTime);

    public function setSortOrder($sortOrder);

    public function setIsActive($isActive);
}
