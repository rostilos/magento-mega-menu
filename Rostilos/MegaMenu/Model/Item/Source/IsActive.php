<?php
namespace Rostilos\MegaMenu\Model\Item\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsActive implements OptionSourceInterface
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
        $availableOptions = $this->item->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
