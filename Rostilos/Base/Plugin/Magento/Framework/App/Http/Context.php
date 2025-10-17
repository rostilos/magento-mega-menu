<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\Base\Plugin\Magento\Framework\App\Http;

use Magento\Framework\App\Http\Context as HttpContext;

class Context
{
    public function beforeGetVaryString(HttpContext $subject)
    {

        $detect = new \Detection\MobileDetect();
        $device = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');
        $subject->setValue('user_device', $device, 'default');
    }
}
