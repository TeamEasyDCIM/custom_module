<?php

namespace Modules\Addons\CustomModule\Controller;

use App\Models\Device;
use Components\Libs\Grid\DataTableResponse;
use Components\Libs\Grid\GridTableGenerator;
use Components\NetConf\NetConf;

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
        $devices = Device::where(['type_id' => 4]);

        // Create new table generator instance
        $table = new GridTableGenerator($devices);
        $table->setColumns(configuration_get('CustomModule::devices.columns.device'));
        $table->addAdditionalParam('editURL', route('backend.custom.module.tab1.edit.quick'));

        // Check whether you need a JSON response
        if ($this->wantsCleanJsonResponse()) {
            $dataResponse = new DataTableResponse($devices);
            $dataResponse->setColumns(configuration_get('CustomModule::devices.columns.device'));

            return response_json($dataResponse->show());
        }

        return view('CustomModule::tab1.summary', ['table' => $table]);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function enableAction($id)
    {
        $device = Device::findOrFail($id);

        /**
         * Device Metadata
         */
        $metadata = $device->getMetaListAttribute();

        echo '<pre>';
        print_r($metadata);
        echo '</pre>';

        /**
         * Device Ports
         * Database Query https://laravel.com/docs/4.2/queries
         */
        $users = \DB::table('item_ports')->where('item_id', $id)->get();

        echo '<pre>';
        print_r($users);
        echo '</pre>';
    }

    /**
     * @param $id
     * @return mixed
     */
    public function disableAction($id)
    {
        $device = Device::findOrFail($id);

        echo '<pre>';
        print_r($device->toArray());
        echo '</pre>';
        die();
    }

    /**
     * @return mixed
     */
    public function quickEdit()
    {
        $id = input_get('pk');
        $column = input_get('name');
        $value = input_get('value');

        $device = Device::findOrFail($id);
        $device->{$column} = e($value);

        if ($device->save()) {
            return response_json([
                'status' => 'success',
                'message' => trans('backend/global.success'),
                'newValue' => $device->getPresenterValue($column)
            ]);
        }

        return response_json([
            'status' => 'error',
            'message' => $device->validationBag()->first()
        ]);
    }

    /**
     * Display API v3 examples.
     *
     * The sample module no longer shows API v2/apikey calls because API v2 is deprecated.
     * Use Sanctum bearer tokens and the routes in Controller/Api/v3/ExampleController instead.
     *
     * @return \View
     */
    public function apiRequest()
    {
        return view('CustomModule::tab2.summary', [
            'endpoints' => [
                ['method' => 'GET', 'path' => '/api/v3/admin/custom-module/status', 'description' => 'Module status'],
                ['method' => 'GET', 'path' => '/api/v3/admin/custom-module/stats', 'description' => 'Device and service counters'],
                ['method' => 'GET', 'path' => '/api/v3/admin/custom-module/devices?per_page=25', 'description' => 'Paginated devices list'],
                ['method' => 'GET', 'path' => '/api/v3/admin/custom-module/devices/{id}', 'description' => 'Single device details'],
                ['method' => 'PATCH', 'path' => '/api/v3/admin/custom-module/devices/{id}/metadata', 'description' => 'Update selected metadata values'],
                ['method' => 'GET', 'path' => '/api/v3/admin/custom-module/settings', 'description' => 'Read module settings'],
                ['method' => 'PUT', 'path' => '/api/v3/admin/custom-module/settings', 'description' => 'Save module settings'],
            ],
        ]);
    }
}
