<?php
namespace Rostilos\MegaMenu\Helper;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Rostilos\MegaMenu\Model\Group;
use Rostilos\MegaMenu\Model\Item;
use Rostilos\MegaMenu\Model\Item\Image as ImageModel;
use Rostilos\Base\Helper\Data as BaseHelper;
use stdClass;


class Mega extends AbstractHelper
{
    protected BaseHelper $baseHelper;
    protected ImageModel $imageModel;

    protected $children = [];

    protected $items = [];

    protected $active_tree = [];

    protected $param = null;

    protected $menuHtml = null;

    protected $deviceType = null;

    protected CategoryFactory $categoryFactory;

    /**
     * Mega constructor.
     * @param Context $context
     * @param BaseHelper $baseHelper
     * @param CategoryFactory $categoryFactory
     * @param ImageModel $imageModel
     */
    public function __construct(
        Context $context,
        BaseHelper $baseHelper,
        CategoryFactory $categoryFactory,
        ImageModel $imageModel
    ) {
        $this->baseHelper = $baseHelper;
        $this->categoryFactory = $categoryFactory;
        $this->imageModel = $imageModel;
        $this->param = new \stdClass();

        parent::__construct($context);
    }

    public function setParams($params = [])
    {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $this->param->$key = $value;
            }
        }
    }

    public function getParam($key = null)
    {
        $rs = null;
        if ($key) {
            if (isset($this->param->$key)) {
                $rs = $this->param->$key;
            }
        } else {
            $rs = $this->param;
        }

        return $rs;
    }

    /**
     * @param $items
     * @param $deviceType
     * @return bool
     * @throws LocalizedException
     */
    public function rebuildData($items, $deviceType = null): bool
    {

        $this->deviceType = $deviceType;

        //for developers only
        $device = $this->baseHelper->getRequest()->getParam('device', null);
        if ($device && in_array($device, ['mobile', 'tablet', 'desktop'])) {
            $this->deviceType = $device;
        }

        //built tree ids of each item
        $temp = array();
        foreach ($items as $item) {
            $child = [];
            if (isset($temp[$item->getParentId()])) {
                $child = $temp[$item->getParentId()];
            }
            array_push($child, $item->getId());
            $temp[$item->getId()] = $child;
            $item->setData('tree', $child);
        }

        $children = [];
        foreach ($items as $item) {
            $pt = (int)$item->getParentId();
            $list = isset($children[$pt]) ? $children[$pt] : [];
            //build some mega params
            $megaParam = new stdClass();
            $megaParam->device = $this->deviceType;
            $megaParam->cols = $item->getMegaCols() ? $item->getMegaCols() : 1;
            $megaParam->is_group = $item->isGroup();
            $megaParam->class = $item->getAdditionClass();
            $megaParam->width = $item->getMegaWidth();
            $megaParam->col_width = $item->getMegaColWidth(); // width of a column
            $megaParam->base_width_type = $item->getMegaBaseWidthType(); // base width type: 1-pixel or 2-percent
            $megaParam->visible_in = $this->getVisibleIn($item);
            $megaParam->sub_content_type = $item->getMegaSubContentType();
            $megaParam->desc = '';

            //set data category thumbnail
            if ($item->getData('level') != 1 && $item->getData('link_type') == 'category-page' &&
                $item->getData('is_show_category_thumb')) {

                $categoryId = (int)$item->getData('category_id');
                $imgCate = $this->_categoryFactory->create()->load($categoryId)->getImageUrl();
                if ($imgCate) {
                    $megaParam->thumb = $imgCate;
                }
            }

            //get mega col_x width
            /**
             * Example format:
             * col1=50
             * col2=100
             * col3=50
             */
            if (preg_match_all('/([^\s]+)=([^\s]+)/',
                $item->getMegaColXWidth() ? $item->getMegaColXWidth() : '',
                $colwMatches)) {
                for ($i = 0; $i < count($colwMatches[0]); $i++) {
                    $attrCol = (string)$colwMatches[1][$i];
                    $megaParam->$attrCol = $colwMatches[2][$i];
                }
            }

            $item->setData('mega_param', $megaParam);

            //only show mega contents (desc, Custom content, Static Blocks of Menu item) as settings
            if (in_array($this->deviceType, $megaParam->visible_in)) {
                $megaParam->desc = $item->getDescription();
                $blocks = $this->loadBlocks($item);
                if (is_array($blocks) && count($blocks) > 0) {
                    $content = '';
                    $item->setData('content', $content);
                    $total = count($blocks);
                    $cols = min($item->getData('mega_param')->cols, $total);
                    for ($col = 0; $col < $cols; $col++) {
                        $pos = ($col == 0) ? 'first' : (($col == $cols - 1) ? 'last' : '');
                        if ($cols > 1)
                            $content .= $this->beginSubMenuContent($item, 1, $pos, $col, true);
                        $i = $col;
                        while ($i < $total) {
                            $block = $blocks[$i];
                            $i += $cols;
                            $content .= $block->toHtml();
                        }
                        if ($cols > 1)
                            $content .= $this->endSubMenuContent($item, 1, true);
                    }
                    $item->setData('cols', $cols);
                    $item->setData('content', trim($content));
                    $this->items[$item->getId()] = $item;
                } elseif ($blocks) { //custom content html
                    $item->setData('content', $blocks);
                }
            }

            if ($megaParam->cols) {
                $item->setData('cols', $megaParam->cols);
                for ($i = 1; $i <= $megaParam->cols; $i++) {
                    if (isset($megaParam->{"col{$i}"}) && $megaParam->{"col{$i}"}) {
                        $item->setData("col{$i}", $megaParam->{"col{$i}"});
                    }
                }
            }

            $item->setData('_idx', count($list));
            array_push($list, $item);
            $children[$pt] = $list;

            $this->items[$item->getId()] = $item;
        }

        $this->children = $children;

        return true;
    }


    /**
     * Generate menu html
     *
     * @param int $pid
     * @param int $level
     * @return string
     */
    public function genMenu($pid = 0, $level = 0, $deviceType = null)
    {
        if($deviceType){
            $this->deviceType = $deviceType;
        }
        if (!isset($this->children[$pid])) {
            return '';
        }
        $this->menuHtml = null;
        $this->genMenuItems($pid, $level);

        return $this->menuHtml;
    }

    public function genMenuItems($pid, $level)
    {
        if (isset($this->children[$pid]) && $this->children[$pid]) {
            $cols = ($pid && $this->items[$pid]->getData('cols')) ? $this->items[$pid]->getData('cols') : 1;
            $total = count($this->children[$pid]);

            $om = $this->baseHelper->getObjectManager();
            $tmp = isset($this->items[$pid])
                ? $this->items[$pid]
                : $om->create('Rostilos\MegaMenu\Model\Item');

            // calculate number of menu items per column
            if ($cols > 1) {
                $fixItems = 0;
                if ($fixItems < $cols) {
                    $leftItem = $total - $fixItems;
                    $col = [];
                    $items = ceil($leftItem / ($cols - $fixItems));
                    for ($m = 0; $m < $cols && $leftItem > 0; $m++) {
                        $colTmp = $tmp->getData('col');
                        if (!isset($colTmp[$m]) || !$colTmp[$m]) {
                            if ($leftItem > $items) {
                                $col[$m] = $items;
                                $leftItem -= $items;
                            } else {
                                $col[$m] = $leftItem;
                                $leftItem = 0;
                            }
                        }
                    }
                    $tmp->setData('col', $col);
                    $cols = count($col);
                    $tmp->setData('cols', $cols);
                }
            } else {
                $tmp->setData('col', [$total]);
            }

            //recalculate the colw for this column if the first child is group
            for ($col = 0, $j = 0; $col < $cols && $j < $total; $col++) {
                $i = 0;
                $colw = 0;
                $colTmp = $tmp->getData('col');
                while ($i < $colTmp[$col] && $j < $total) {
                    $row = isset($this->children[$pid][$j]) ? $this->children[$pid][$j] : null;
                    if ($row->getData('mega_param')->is_group && $row->getData('mega_param')->width > $colw) {
                        $colw = $row->getData('mega_param')->width;
                    }
                    $j++;
                    $i++;
                }
                if ($colw) {
                    if (isset($this->items[$pid])) {
                        $this->items[$pid]->getData('mega_param')->{'col' . ($col + 1)} = $colw;
                    }
                }
            }

            $this->beginMenuItems($pid, $level);

            for ($col = 0, $j = 0; $col < $cols && $j < $total; $col++) {
                $pos = ($col == 0) ? 'first' : (($col == $cols - 1) ? 'last' : '');
                //recalculate the colw for this column if the first child is group
                if ($this->children[$pid][$j]->getData('mega_param')->is_group
                    && $this->children[$pid][$j]->getData('mega_param')->width) {
                    $this->items[$pid]->getData('mega_param')->{'col' . ($col + 1)} = $this->children[$pid][$j]->getData('mega_param')->width;
                }

                $this->beginSubMenuItems($pid, $level, $pos, $col);

                $i = 0;
                $colTmp = $tmp->getData('col');
                while ($i < $colTmp[$col] && $j < $total) {
                    $row = isset($this->children[$pid][$j]) ? $this->children[$pid][$j] : null;
                    $pos = ($i == 0) ? 'first' : (($i == count($this->children[$pid]) - 1) ? 'last' : '');

                    $this->beginMenuItem($row, $level, $pos);
                    $this->genMenuItem($row, $level, $pos);

                    // show menu with menu expanded - sub-menus visible
                    /*if (isset($row->getData('mega_param')->is_group) && $row->getData('mega_param')->is_group) {
                        $this->genMenuItems($row->getId(), $level); //not increase level
                    } elseif ($level < $this->param->end_level) {
                        $this->genMenuItems($row->getId(), $level + 1);
                    }*/

                    if ($level < $this->param->end_level) {
                        $this->genMenuItems($row->getId(), $level + 1);
                    }

                    $this->endMenuItem($row, $level, $pos);
                    $j++;
                    $i++;
                }

                $this->endSubMenuItems($pid, $level);
            }

            $this->endMenuItems($pid, $level);
        }
    }

    public function beginMenuItems($pid = 0, $level = 0, $return = false)
    {
        $data = '';
        if ($level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            if (!$megaParam->is_group) {
                $style = $this->param->mega_style;
                $data = call_user_func_array(array($this, "beginMenuItems$style"), array($pid, $level, true));
            } else {
                $data = '';
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function beginMenuItems1($pid = 0, $level = 0, $return = false)
    {
        $megaParam = $this->items[$pid]->getData('mega_param');
        $cols = ($pid && isset($megaParam->cols) && $megaParam->cols) ? $megaParam->cols : 1;
        $widthSuffix = ($megaParam->base_width_type == Item::BASE_WIDTH_PIXEL_TYPE)
            ? 'px'
            : '%';
        $width = ($widthSuffix === '%') ? 100 : $megaParam->width;
        if (!$width) {
            for ($col = 0; $col < $cols; $col++) {
                $colxName = 'col' . ($col + 1);
                $colw = property_exists($megaParam, $colxName) ? $megaParam->$colxName : null;
                if (!$colw) {
                    $colw = ($megaParam->col_width)
                        ? $megaParam->col_width
                        : (($megaParam->base_width_type == Item::BASE_WIDTH_PIXEL_TYPE)
                            ? $this->param->default_mega_col_width
                            : (100/$cols));
                }
                //update width of container
                $width += $colw + $this->param->mega_col_margin;
            }
        }
        $style = $width ? " style=\"width: {$width}{$widthSuffix};\"" : "";
        $right = isset($this->items[$pid]->getData('mega_param')->right) ? ' right' : '';

        $isActivated = in_array($pid, $this->active_tree);
        $activeClass = ($isActivated) ? ' active' : '';

        if ($this->items[$pid]->getData('show_title')
            && $this->param->mobile_type === \Rostilos\MegaMenu\Model\Group::TYPE_DRILLDOWN) {
            $data = "<div class=\"child-content cols{$cols}{$right}{$activeClass} drilldown-sub\">\n";
        } else {
            $data = "<div class=\"child-content cols{$cols}{$right}{$activeClass}\">\n";
        }
        $data .= "<div id=\"child-content-{$pid}\" class=\"child-content-inner\"{$style}>"; //move width into inner

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function endMenuItems1($pid = 0, $level = 0, $return = false)
    {
        $data = "</div>\n"; //close of child-content-inner
        $data .= "</div>"; //close child-content
        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function endMenuItems($pid = 0, $level = 0, $return = false)
    {
        if ($level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            if (!$megaParam->is_group) {
                $style = $this->param->mega_style;
                $data = call_user_func_array(array($this, "endMenuItems{$style}"), array($pid, $level, true));
            } else {
                $data = '';
            }
            if ($return) {
                return $data;
            } else {
                $this->menuHtml .= $data;
            }
        }
    }

    public function beginSubMenuContent($item, $level, $pos, $i, $return = false)
    {
        $data = '';
        $megaParam = $item->getData('mega_param');
        $cols = (isset($megaParam->cols) && $megaParam->cols) ? $megaParam->cols : 1;
        $widthSuffix = ($megaParam->base_width_type == Item::BASE_WIDTH_PIXEL_TYPE)
            ? 'px' : '%';
        if ($level) {
            if (!$megaParam->is_group) {
                $colxName = 'col' . ($i + 1);
                $colw = (isset($megaParam->$colxName)) ? $megaParam->$colxName : null;
                if (!$colw) {
                    $parentItem = $this->items[$item->getParentId()];
                    $colw = ($parentItem->getData('mega_param')->col_width)
                        ? $parentItem->getData('mega_param')->col_width
                        : (($parentItem->getData('mega_param')->base_width_type == Item::BASE_WIDTH_PIXEL_TYPE)
                            ? $this->param->default_mega_col_width
                            : (100/$cols));
                }
                $style = $colw ? " style=\"width: {$colw}{$widthSuffix};\"" : "";
                $data .= "<div class=\"mega-col column" . ($i + 1) . ($pos ? " $pos" : "") . "\"$style>";
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function endSubMenuContent($item, $level = 0, $return = false)
    {
        $data = '';
        if ($level) {
            if (!$item->getData('mega_param')->is_group) {
                $data .= "</div>";
            }
        }

        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    /**
     * @param $pid
     * @param $level
     * @param $pos
     * @param $i
     * @param $return
     * @return string
     */
    public function beginSubMenuItems($pid, $level, $pos, $i, $return = false): string
    {
        $level = (int)$level;
        $data = '';
        $drillDownButtons = null;
        if (isset($this->items[$pid]) && $level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            $cols = ($pid && isset($megaParam->cols) && $megaParam->cols) ? $megaParam->cols : 1;
            $widthSuffix = ($megaParam->base_width_type == Item::BASE_WIDTH_PIXEL_TYPE)
                ? 'px'
                : '%';

            //build drilldown buttons if parent item is Group
            if ($this->items[$pid]->getData('show_title')
                && $this->param->mobile_type === Group::TYPE_DRILLDOWN) {
                $drillDownButtons = '';
            }

            if ($cols > 1) {
                $colw = 0;
                $colxName = 'col' . ($i + 1);
                if (isset($megaParam->$colxName))
                    $colw = $megaParam->$colxName;
                if (!$colw) {
                    $colw = ($megaParam->col_width)
                        ? $megaParam->col_width
                        : (($megaParam->base_width_type == Item::BASE_WIDTH_PIXEL_TYPE)
                            ? $this->param->default_mega_col_width
                            : (100 / $cols));
                }
                $style = $colw ? " style=\"width: {$colw}{$widthSuffix};\"" : "";
                $data .= "<div class=\"mega-col column" . ($i + 1) . ($pos ? " $pos" : "") . "\"$style>";
            }
        }

        if (isset($this->children[$pid]) && $this->children[$pid]) {
            //if is first level (level = 0)
            if ($level === 0) {
                if ($this->param->show_menu_title) {
                    $this->menuHtml .= '<h3 class="rostilos-mega-menu-title">' . $this->param->menu_title . '</h3>';
                }
                $menuGroupId = $this->param->menu_group_id;
                $extraClasses = ($this->param->addition_class)
                    ? "{$this->param->addition_class} "
                    : '';
                $menuIdAttr = "id='rostilos-mega-menu-{$menuGroupId}-{$this->deviceType}'";
                $deviceTypeAttr = "data-device-type='{$this->deviceType}'";
                $ulClasses = "{$this->param->animation} rostilos-mega-menu level{$level} {$extraClasses}";
            } else {
                $menuIdAttr = '';
                $deviceTypeAttr = '';
                $ulClasses = "level{$level}";
                if (!is_null($drillDownButtons)) {
                    $ulClasses .= " drilldown-sub";
                }
            }
            $data .= "<ul {$menuIdAttr} class=\"{$ulClasses}\" $deviceTypeAttr>{$drillDownButtons}";
        }

        if ($return) {
            return $data;
        } else {
            return $this->menuHtml .= $data;
        }
    }
    public function endSubMenuItems($pid = 0, $level = 0, $return = false)
    {
        $data = '';
        if ($this->children[$pid]) {
            $data .= "</ul>";
        }
        if (isset($this->items[$pid]) && $level) {
            $megaParam = $this->items[$pid]->getData('mega_param');
            $cols = ($pid && isset($megaParam->cols) && $megaParam->cols) ? $megaParam->cols : 1;
            if ($cols > 1) {
                $data .= "</div>";
            }
        }
        if ($return) {
            return $data;
        } else {
            $this->menuHtml .= $data;
        }
    }

    public function beginMenuItem($item = null, $level = 0, $pos = '')
    {
        $class = $this->genClass($item, $level, $pos);
        $id = 'id="menu' . $item->getId() . '"';
        if ($class) {
            $class = "class=\"{$class}\"";
        }
        $this->menuHtml .= "<li {$class} {$id}>";
    }

    public function endMenuItem($item = null, $level = 0, $pos = '')
    {
        $this->menuHtml .= "</li>";
    }

    /**
     * @param $item
     * @param int $level
     * @param string $pos
     * @param int $return
     * @return string
     * @throws NoSuchEntityException
     */
    /**
     * @param $item
     * @param int $level
     * @param string $pos
     * @param int $return
     * @return string
     * @throws NoSuchEntityException
     */
    public function genMenuItem($item, $level = 0, $pos = '', $return = 0): string
    {
        /* @var Item $item */
        $data = '';
        $menuText = ($item->isShowTitle()) ? $item->getTitle() : '';
        $menuTitle = ($item->getSEOTitle()) ? $item->getSEOTitle() : $item->getTitle();
        $titleAttr = null;
        $productCountingText = '';
        $class = $this->genClass($item, $level, $pos, true);
        if ($class) {
            $class = " class=\"$class\"";
        }
        $hasChild = substr_count($class, 'has-child');

        //check has active
        $isActivated = in_array($item->getId(), $this->active_tree);
        //check has disable menu link
        $isDisabledLink = ($item->getLinkType() === Item::LINK_TYPE_NO_LINK) ? 1 : 0;

        //check show number products in menu title
        if ($item->getLinkType() == Item::LINK_TYPE_CATEGORY) {
            /* @var CategoryRepositoryInterface $categoryRepository */
            $categoryRepository = $this->baseHelper->getObjectManager()
                ->get('Magento\Catalog\Api\CategoryRepositoryInterface');
            /* @var CategoryInterface $category */
            try {
                $category = $categoryRepository->get(
                    $item->getCategoryId(),
                    $this->baseHelper->getStoreManager()->getStore()->getId()
                );
            } catch (NoSuchEntityException $e) {
                $category = false;
            }
            if ($category) {
                //update menu item link by store
                $item->setLink($category->getUrl());
                if ($item->isShowTitle() && $this->isShowNumberProduct($item)) {
                    $productCountingText = '<span class="number-product">(' . $category->getProductCount() . ')</span>';
                }
            }
        }

        //check show menu title
        if ($menuText || $item->getFontAwesome() || $item->getIconImage()) {
            // add font awesome
            if ($item->getFontAwesome()) {
                $menuText = '<i class="' . $item->getFontAwesome() . '"></i>' . $menuText;
            }
            //add title text
            if ($menuText) {
                if (!$hasChild) {
                    $menuText .= $productCountingText;
                }
            }
            //add menu icon image
            if ($item->getIconImage()) {
                $icon = '<span class="menu-icon"><img alt="' . strip_tags($menuTitle)
                    . '" src="' . $this->imageModel->getBaseUrl() . $item->getIconImage() . '" /></span> ';
                $menuText = $icon . $menuText;
            }
        }

        //reprocess for urls which has shortcode {base_url}
        $link = $item->getLink();
        if ($link && !preg_match('#^(http|https)://.+(\.[a-z]{2,5})?$#', $link)) { // is not full link
            $baseUrl = $this->baseHelper->getStoreManager()->getStore()->getBaseUrl();
            $isBaseUrlMarked = strpos($link, "{base_url}");
            if (($item->getLinkType() == Item::LINK_TYPE_CUSTOM) && $isBaseUrlMarked >= 0) {
                $link = str_replace('{base_url}', $baseUrl, $link);
            } else {
                $link = $baseUrl . $link;
            }
            //update full link
            $item->setLink($link);
        }

        //add data thumbnail category
        $megaParam = $item->getData('mega_param');
        if (isset($megaParam->thumb) && $megaParam->thumb) {
            $data .= '<div class="category-thumb"><a href="' . $link . ' "' . $titleAttr
                . '/><img src="' . $megaParam->thumb . '" alt="' . $menuTitle . '"/></a></div>';
        }
        if ($menuText) {
            if (!$isDisabledLink) {
                //add target to tag a
                switch ($item->getLinkTarget()) {
                    default:
                    case '_top':
                        $data .= '<a href="' . $link . '" ' . $class . ' ' . $titleAttr . '>'
                            . $menuText . '</a>';
                        break;
                    case '_blank':
                        $data .= '<a href="' . $link . '" target="_blank" ' . $class . ' ' . $titleAttr . '>'
                            . $menuText . '</a>';
                        break;
                    case '_parent':
                        $data .= '<a href="' . $link . '" target="_parent" ' . $class . ' ' . $titleAttr . '>'
                            . $menuText . '</a>';
                        break;
                }
            } else {
                $data .= '<span ' . $class . ' ' . $titleAttr . '>' . $menuText . '</span>';
            }
        }

        //add description to menu item
        if (isset($megaParam->desc) && $megaParam->desc) {
            if ($this->deviceType !== 'mobile' // is tablet or desktop devices
                || ($this->deviceType === 'mobile' && $this->param->mobile_type // is mobile device with accordion
                    === Group::TYPE_ACCORDION)) {
                $desc = $this->baseHelper->getObjectManager()->create(
                    'Rostilos\Base\Model\TemplateFilter'
                )->filter($megaParam->desc);
                $data .= '<div class="menu-desc">' . $desc . '</div>';
            }
        }

        //append 'shop 'al item if item has child
        if ($this->deviceType !== 'desktop'
            && $this->param->mobile_type !== Group::TYPE_DRILLDOWN
            && $hasChild) {
            if ($link && strlen(trim($link)) && $link != '#') {
                $data .= '<span class="menu-group-link' . (($isActivated) ? ' active' : '')
                    . '" >' . __('Shop all') . '</span>';
            }
        }

        if ($this->param->is_mega_menu) {
            if ($item->getData('content')) {
                if ($megaParam->is_group) {
                    $data .= $item->getData('content');
                } else {
                    $data .= $this->beginMenuItems($item->getId(), $level + 1, true);
                    $data .= $item->getData('content');
                    $data .= $this->endMenuItems($item->getId(), $level + 1, true);
                }
            }
        }

        if ($return) {
            return $data;
        } else {
            return $this->menuHtml .= $data;
        }
    }

    public function loadBlocks($item)
    {
        $blocks = array();
        $subContentType = $item->getMegaSubContentType();
        switch ($subContentType) {
            case 'static-block':
                $ids = $item->getStaticBlocks();
                $ids = preg_split('/,/', $ids);
                foreach ($ids as $id) {
                    if ($id) {
                        $storeId = $this->baseHelper->getStoreManager()->getStore()->getId();
                        $block = $this->baseHelper->getObjectManager()->get(
                            'Magento\Cms\Model\Block'
                        )->setStoreId($storeId)->load($id);
                        $layout = $this->baseHelper->getObjectManager()->get(
                            '\Magento\Framework\View\LayoutInterface'
                        );
                        $block = $layout->createBlock('Magento\Cms\Block\Block')->setBlockId($block->getIdentifier());
                        $blocks[] = $block;
                    }
                }
                return $blocks;
            case 'custom-content':
                $block = $this->baseHelper->getObjectManager()->create(
                    'Rostilos\Base\Model\TemplateFilter'
                )->filter($item->getCustomContent());
                return $block;
            default:
                return null;
        }
    }

    /**
     * @param $item
     * @param $level
     * @param $pos
     * @return string
     */
    public function genClass($item, $level, $pos, $isAnchor = false)
    {
        $cls = "mega" . ($pos ? " $pos" : "");
        if ($item) {
            $megaParam = $item->getData('mega_param');
            if (isset($this->children[$item->getId()]) || ($item->hasData('content') && $item->getData('content'))) {
                if ($megaParam->is_group && !$isAnchor) {
                    $cls .= " group";
                } elseif ($level < $this->param->end_level) {
                    $cls .= " has-child";
                }
                if ($megaParam->base_width_type == Item::BASE_WIDTH_PERCENT_TYPE) {
                    $cls .= " dynamic-width";
                }
            }

            //check current active and add active class
            if (isset($this->active_tree)) {
                $active = in_array($item->getId(), $this->active_tree);
            } else {
                $active = false;
            }
            /*if (!preg_match('/group/', $cls)) {
                $cls .= ($active ? " active" : "");
            }*/
            $cls .= ($active ? " active" : "");

            //add addition class to menu item
            if ($megaParam->class) {
                $cls .= " " . $megaParam->class;
            }
        }

        return $cls;
    }

    /**
     * @param $item
     * @return bool
     */
    protected function isShowNumberProduct($item)
    {
        $globalConfig = $this->getParam('show_number_product');
        if ($item->isShowNumberProduct() == Item::SHOW_NUMBER_PRODUCT_YES
            || ($item->isShowNumberProduct() == Item::SHOW_NUMBER_PRODUCT_USE_GENERAL_CONFIG
                && $globalConfig)
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $item
     * @return array|null
     */
    protected function getVisibleIn($item)
    {
        $globalConfig = $this->getParam('mega_content_visible_in');
        if ($item->getVisibleOption() == Item::VISIBLE_OPTION_USE_GENERAL_CONFIG
            && $globalConfig) {
            $rs = $globalConfig;
        } else {
            $rs = $item->getVisibleIn();
        }

        if ($rs) { //format: desktop,tablet,mobile
            $rs = explode(',', $rs);
        } else {
            $rs = [];
        }

        return $rs;
    }
}
