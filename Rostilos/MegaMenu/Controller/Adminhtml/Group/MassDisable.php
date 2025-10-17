<?php
/**
 * Copyright © 2016 Rostilos.com All rights reserved.
 */
namespace Rostilos\MegaMenu\Controller\Adminhtml\Group;

use Rostilos\MegaMenu\Model\Group;

class MassDisable extends MassEnable
{
    /**
     * @var string success message
     */
    protected $successMessage = 'A total of %1 record(s) have been disabled';

    /**
     * @var string error message
     */
    protected $errorMessage = 'An error occurred while disabling record(s).';

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @param Group $group
     * @return $this|MassEnable
     * @throws \Exception
     */
    protected function runAction(Group $group)
    {
        $group->setIsActive($this->isActive);
        $group->save();
        return $this;
    }
}
