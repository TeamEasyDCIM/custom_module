<?php

namespace Modules\Addons\CustomModule;

use Components\Modules\Core\AddonModule;
use Components\Modules\Interfaces\ModuleInterface;

/**
 * Class CustomModule
 * @package Modules\Addons\CustomModule
 */
class CustomModule extends AddonModule implements ModuleInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Custom Module';
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return 'custom-module';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Custom Module Description';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDocumentation()
    {
        return 'http://www.docs.easydcim.com';
    }

    /**
     * Module Author
     *
     * @return string
     */
    public function getAuthor()
    {
        return 'EasyDCIM';
    }

    /**
     * Event will fired on module activate
     *
     * @return void
     */
    public function onActivate() {}

    /**
     * Event will fired on module deactivate
     *
     * @return void
     */
    public function onDeactivate() {}
}