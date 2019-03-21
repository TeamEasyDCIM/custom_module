<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class ItemPortsController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class ItemPortsController extends OutputController
{
    /**
     * @return string|void
     */
    public function createPort()
    {
        $deviceId = 137;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/device/%s/port', [$deviceId]));

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
                        'port_number' => 1,
                        'port_label' => 'eth0',
                        'description' => 'eth0',
                        'physaddress' => '00:0a:95:9d:68:16',
                        'if_type' => \ItemPort::IF_TYPE_GIGABITETHERNET
                    ],
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            d($stream->getContents());
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }
}