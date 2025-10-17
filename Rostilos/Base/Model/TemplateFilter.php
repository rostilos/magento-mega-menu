<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 *
 */

namespace Rostilos\Base\Model;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Template\FilterProvider;

class TemplateFilter
{
    protected FilterProvider $filterProvider;
    protected BlockFactory $blockFactory;

    public function __construct(
        FilterProvider $filterProvider,
        BlockFactory $blockFactory
    )
    {
        $this->filterProvider = $filterProvider;
        $this->blockFactory = $blockFactory;
    }

    public function filter($content)
    {
        return $this->filterProvider->getBlockFilter()->filter($content);
    }
}
