<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class DevicesController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class DevicesController extends OutputController
{
    /**
     * @return string|void
     */
    public function listDevices()
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
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'filters' => [
                        'type_id' => 4,
                        'order_id' => 1,
                        'id' => 153,
                        'user_id' => 1,
                        'model_id' => 1,
                        'location_id' => 1,
                        'rack_id' => 1,
                        'serialnumber1' => 'Serialnumber',
                        'serialnumber2' => 'Serialnumber',
                        'service_tag' => 'Service Tag',
                        'size' => 2,
                        'label' => 'Sample Label',
                        'colocation_id' => 1,
                        'locked' => 'yes',
                        'parent_id' => 1,
                        'metadata_key' => 1,
                        'metadata_value' => '192.168.56.100',
                        'status' => 'available',
                        'device_status' => 'running',
                        'comments' => 'Comments',
                        'is_part' => 1,
                        'mountable' => 1
                    ],
                    'relations' => [
                        'user',
                        'itemModel',
                        'order',
                        'location',
                        'rack',
                        'metadata',
                        'type',
                        'selfParent',
                    ],
                    'pagination' => [
                        'page' => 1,
                        'perPage' => 20
                    ]
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            d($stream->getContents());
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }

    /**
     * @return string|void
     */
    public function showDevice()
    {
        $deviceId = 171;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/device/%s', [$deviceId]));

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \App\Models\User::first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'relations' => [
                        'user',
                        'itemModel',
                        'order',
                        'location',
                        'rack',
                        'metadata',
                        'type',
                        'selfParent'
                    ],
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            d($stream->getContents());
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }

    /**
     * @return string|void
     */
    public function createDevice()
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
                        'type' => 4,
                        'model' => 'Dell PowerEdge R210',
                        'image' => 'DELL-R210-FRONT.PNG', // images from /opt/easydcim/public/images/devices
                        'location_id' => 2,
                        'user_id' => 1,
                        'rack_id' => 3,
                        'is_part' => 0,
                        'mountable' => 1,
                        'manufacturer' => 'Dell',
                        'size' => 2,
                        'device_status' => 'running', // running|halted|rebooted
                        'serialnumber1' => 'SERIAL 1',
                        'serialnumber2' => 'SERIAL 2',
                        'service_tag' => 'Service Tag',
                        'comments' => 'Production Server',
                        'notes' => 'Quick Note',
                        'description' => 'Server Description',
                        'warranty_months' => 24,
                        'warranty_info' => 'Warranty Information',
                        'status' => 'bought', // available|bought|defect|in use|not_delivered|sold|repair
                        'buy_price' => 240,
                        'locked' => 0,
                        'poller_disabled' => 0,
                        'size_position' => 'full' // full|front|rear
                    ],
                    'metadata' => [
                        'IP Address' => '192.168.200.10',
                        'Hostname' => 'server.local',
                        'Additional IP Addresses' => '192.168.200.11,192.168.200.12,192.168.200.13,192.168.200.14',
                        'MAC Address' => '0a:00:27:00:00:02'
                    ]
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            d($stream->getContents());
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function bootDevice()
    {
        $deviceId = 135;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/device/%s/power/up', [$deviceId]));

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
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            d($stream->getContents());
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function rebootDevice()
    {
        $deviceId = 135;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/device/%s/power/reboot', [$deviceId]));

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
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            d($stream->getContents());
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function shutdownDevice()
    {
        $deviceId = 135;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/device/%s/power/shutdown', [$deviceId]));

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
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            d($stream->getContents());
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }
}