<?php

namespace Modules\Addons\CustomModule\Controller;

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
     * @param $id
     * @return mixed
     */
    public function enableAction($id)
    {
        $device = \Device::findOrFail($id);

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

        /**
         * Netconf Samples
         */
        $netconf = $device->netconf();

        if($netconf instanceof NetConf) {
            $command = $netconf->sendRPC(vsprintf('
<load-configuration action="set">
<configuration-set>set interfaces %s disable</configuration-set>
</load-configuration>', ['interface-name']));

            $commandErrorMessage = array_get($command->getResponseArray(), 'load-configuration-results.rpc-error.error-message');

            if(! empty($commandErrorMessage)) {
                echo '<pre>';
                print_r($command->getRPCError());
                echo '</pre>';
                die();
            }

            $commit = $netconf->commit();

            echo '<pre>';
            print_r($commit->isRPCReplyOK());
            echo '</pre>';
            die();
        }
    }

    /**
     * @param $id
     * @return mixed
     */
    public function disableAction($id)
    {
        $device = \Device::findOrFail($id);

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
            'message' => $device->validationBag()->first()
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

        $apikey = \App\Models\User::first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        $response = $client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'apikey' => $apikey
            ],
        ]);

        $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

        adump($stream->getContents());

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
        $apikey = \App\Models\User::first()->apikey->key;

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


            adump($stream->getContents());
        } catch (\Exception $e) {
            adump($e->getMessage());
        }
    }
}