<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class IpamController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class IpamController extends OutputController
{
    /**
     * @return string|void
     */
    public function listVlans()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/ipam/vlan');

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'filters' => [
                        'group_id' => 1,
                        'number' => 1,
                        'is_private' => 0,
                    ],
                    'relations' => [
                        'servers', 'subnets'
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
    public function showVlan()
    {
        $vlanId = 53;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/ipam/vlan/%s', [$vlanId]));

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'relations' => [
                        'servers', 'subnets'
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
    public function createVlan()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/ipam/vlan');

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
                        'vlan_number' => 11,
                        'vlan_name' => 'Vlan 1 API',
                        'group_id' => 1,
                        'is_private' => 0,
                        'description' => 'Sample Description',
                        'devices' => [153, 2],
                        'subnets' => [5, 3]
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
    public function updateVlan()
    {
        $vlanId = 53;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/ipam/vlan/%s', [$vlanId]));

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('PUT', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'data' => [
                        'vlan_number' => 11,
                        'vlan_name' => 'Vlan 1 API',
                        'group_id' => 1,
                        'description' => 'New Description',
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
    public function deleteVlan()
    {
        $vlanId = 53;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/ipam/vlan/%s', [$vlanId]));

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('DELETE', $url, [
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
     * @return string|void
     */
    public function assignIpAddress()
    {
        $deviceId = 496;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/ipam/device/ip/%s', [$deviceId]));

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
                        'type' => 'primary', // primary or additional or ipmi
                        'address' => '192.168.56.101',
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