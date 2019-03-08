<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class OrdersController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class OrdersController extends OutputController
{
    /**
     * @return string|void
     */
    public function createOrder()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/order');

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
                    'module' => 'Server',
                    'client' => 1,
                    'criteria' => [
                        'model' => 12,
                        'location' => \Location::first()->getAttribute('id'),
                        'require_parts' => 1,
                        'parts' => [
                            8 => ['model' => 16, 'hdd.size' => '1048576'],
                            9 => ['model' => 25, 'ssd.size' => '524288|1048576'],
                            10 => ['model' => 18, 'ram.size' => '8192'],
                            11 => ['model' => 17, 'cpu.cores' => '8-16']
                        ],
                        'require_pdu' => 0,
                        'require_switch' => 0,
                    ],
                    'service' => [
                        'hostname' => 'vps.local',
                        'template' => 'CentOS 7 (latest)',
                        'username' => 'user',
                        'password' => 'pass',
                        'monthly_traffic_limit' => 100,
                        'additional_ips' => '/30',
                        'access_level' => 12,
                    ]
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            s($stream->getContents());
        } catch (\Exception $e) {
            s($e->getMessage());
        }
    }

    /**
     * @return string|void
     */
    public function updateOrder()
    {
        $orderId = 89;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/order/%s/update', [$orderId]));

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
                    'client' => 2,
                    'criteria' => [
                        'model' => 3,
                        'location' => \Location::first()->getAttribute('id'),
                        'require_parts' => 0,
                    ],
                    'service' => [
                        'hostname' => 'test.com',
                        'template' => 'CentOS 7 (latest)',
                        'username' => 'user2',
                        'password' => 'pass2',
                        'monthly_traffic_limit' => 12,
                    ]
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            s($stream->getContents());
        } catch (\Exception $e) {
            s($e->getMessage());
        }
    }

    /**
     * @return string|void
     */
    public function deleteOrder()
    {
        $orderId = 86;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/order/%s', [$orderId]));

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::admins()->first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('DELETE', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            s($stream->getContents());
        } catch (\Exception $e) {
            s($e->getMessage());
        }
    }
}