<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Model\Item\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Menu Group Options
 */
class MenuGroupOptions implements OptionSourceInterface
{
    /**
     * @var \Rostilos\MegaMenu\Model\Item
     */
    protected $item;

    /**
     * Constructor
     *
     * @param \Rostilos\MegaMenu\Model\Item $item
     */
    public function __construct(\Rostilos\MegaMenu\Model\Item $item)
    {
        $this->item = $item;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->item->getMenuGroupOptions();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
