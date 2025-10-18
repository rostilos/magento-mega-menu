<?php
namespace Rostilos\MegaMenu\Model\Group\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Menu Animation Type Options
 */
class AnimationOptions implements OptionSourceInterface
{
    /**
     * @var \Rostilos\MegaMenu\Model\Group
     */
    protected $group;

    /**
     * Constructor
     *
     * @param \Rostilos\MegaMenu\Model\Group $group
     */
    public function __construct(\Rostilos\MegaMenu\Model\Group $group)
    {
        $this->group = $group;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->group->getAnimationTypeOptions();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
