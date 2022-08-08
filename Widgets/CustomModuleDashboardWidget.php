<?php

namespace Modules\Addons\CustomModule\Widgets;

use Components\Libs\Widgets\Boxes\AbstractWidget;
use Components\Libs\Widgets\Boxes\WidgetInterface;

/**
 * Class CustomModuleDashboardWidget
 * @package Modules\Addons\CustomModule\Widgets
 */
class CustomModuleDashboardWidget extends AbstractWidget implements WidgetInterface
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
        return 'panel-stats h-390';
    }

    /**
     * @return string
     */
    public function getType()
    {
        return 'newBox';
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
        return view('CustomModule::widgets.dashboard.summary', []);
    }

    /**
     * @return int
     */
    public function width()
    {
        return 8;
    }

    /**
     * @return int
     */
    public function height()
    {
        return 3;
    }
}