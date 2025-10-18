<?php
namespace Rostilos\MegaMenu\Model\Item\Source;

/**
 * Is active filter source
 */
class IsActiveFilter extends IsActive
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return array_merge([['label' => '', 'value' => '']], parent::toOptionArray());
    }
}
