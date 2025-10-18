<?php
namespace Rostilos\MegaMenu\Model\Config\Source;

class OffcanvasBreakpoint implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'all', 'label' => __('All Devices')],
            ['value' => 'mobile', 'label' => __('Mobile')]
        ];
    }
}
