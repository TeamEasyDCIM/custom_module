<?php

namespace Modules\Addons\CustomModule\Controller;

use Components\Core\Support\Facades\ApiCaller;
use Components\Libs\Grid\DataTableResponse;
use Components\Libs\Grid\GridTableGenerator;

/**
 * Class PageController
 * @package Modules\Addons\CustomModule\Controller
 */
class PageController extends OutputController
{
    /**
     * @return \View
     */
    public function showTab1()
    {
        $devices = \Device::where(['type_id' => 4]);

        // Create new table generator instance
        $table = new GridTableGenerator($devices);
        $table->setColumns(config('CustomModule::devices.columns.device'));

        // Check whether you need a JSON response
        if ($this->wantsCleanJsonResponse()) {
            $dataResponse = new DataTableResponse($devices);
            $dataResponse->setColumns(config('CustomModule::devices.columns.device'));

            return response_json($dataResponse->show());
        }

        return view('CustomModule::tab1.summary', ['table' => $table]);
    }

    /**
     * @return \View
     */
    public function showTab2()
    {
        /**
         * List Item Types From API
         */
        $types = ApiCaller::get('type');

        /**
         * List Locations From API
         */
        $locations = ApiCaller::get('location');

        return view('CustomModule::tab2.summary', ['types' => $types, 'locations' => $locations]);
    }
}