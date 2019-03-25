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
    public function createDevice()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/device');

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::first()->apikey->key;

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
}