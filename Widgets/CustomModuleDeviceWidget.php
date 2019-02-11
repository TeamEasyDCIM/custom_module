<?php

namespace Modules\Addons\CustomModule\Widgets;

use Components\Libs\Widgets\Boxes\AbstractWidget;
use Components\Libs\Widgets\Boxes\WidgetInterface;

/**
 * Class CustomModuleDeviceWidget
 * @package Modules\Addons\CustomModule\Widgets
 */
class CustomModuleDeviceWidget extends AbstractWidget implements WidgetInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return trans('custom-module::backend.widget');
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return 'custom-module::backend.widget';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return trans('custom-module::backend.widget');
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'custom-css-class';
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return true;
    }

    /**
     * @return \View
     */
    public function render()
    {
        /** @var \Device $device */
        $device = $this->getModel();

        return view('CustomModule::widgets.device.summary', ['device' => $device]);
    }

    /**
     * @return int
     */
    public function height()
    {
        return 4;
    }
}