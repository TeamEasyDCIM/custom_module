<?php

namespace Modules\Addons\CustomModule\Controller;

use Components\Libs\Grid\DataTableResponse;
use Components\Libs\Grid\GridTableGenerator;
use Components\NetConf\NetConf;

/**
 * Class DeviceController
 * @package Modules\Addons\CustomModule\Controller
 */
class DeviceController extends OutputController
{
    /**
     * @return \View
     */
    public function showCustomTab($id)
    {
        $item = \Device::findOrFail($id);

        return view('CustomModule::device.tab', [
            'item' => $item,
        ]);
    }
}