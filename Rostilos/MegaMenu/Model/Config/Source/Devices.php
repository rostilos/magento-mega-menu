<?php
namespace Rostilos\MegaMenu\Model\Config\Source;

class Devices implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'desktop', 'label' => __('Desktop')],
            ['value' => 'tablet', 'label' => __('Tablet')],
            ['value' => 'mobile', 'label' => __('Mobile')]
        ];
    }
}
