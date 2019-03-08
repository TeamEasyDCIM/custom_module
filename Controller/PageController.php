<?php

namespace Modules\Addons\CustomModule\Controller;

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
        $table->addAdditionalParam('editURL', route('backend.custom.module.tab1.edit.quick'));

        // Check whether you need a JSON response
        if ($this->wantsCleanJsonResponse()) {
            $dataResponse = new DataTableResponse($devices);
            $dataResponse->setColumns(config('CustomModule::devices.columns.device'));

            return response_json($dataResponse->show());
        }

        return view('CustomModule::tab1.summary', ['table' => $table]);
    }

    /**
     * @return mixed
     */
    public function quickEdit()
    {
        $id = input_get('pk');
        $column = input_get('name');
        $value = input_get('value');

        $device = \Device::findOrFail($id);
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
            'message' => $device->validationErrors()->first()
        ]);
    }

    /**
     * @return \View
     */
    public function apiRequest()
    {
        return view('CustomModule::tab2.summary', []);
    }

    /**
     * @return string|void
     */
    public function getRequest()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/type');

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::admins()->first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'apikey' => $apikey
            ],
        ]);

        $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

        s($stream->getContents());

    }

    /**
     * @return string|void
     */
    public function postRequest()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/device');

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::admins()->first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'data' => [
                        'label' => 'My Device Label',
                        'model' => 'Dell PowerEdge R210',
                        'type_id' => 4
                    ],
                    'metadata' => [
                        'Hostname' => 'myhost.net',
                        'IP Address' => '10.10.10.100',
                        'SNMP Public Community' => 'public',
                        'SNMP Private Community' => 'private'
                    ]
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());


            s($stream->getContents());
        } catch (\Exception $e) {
            s($e->getMessage());
        }
    }
}