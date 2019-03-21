<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class DeviceBaysController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class DeviceBaysController extends OutputController
{
    /**
     * @return string|void
     */
    public function listBays()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/device-bay');

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
                        'parent_id' => 183,
                        'child_id' => 479,
                        'order' => 1,
                        'name' => 'Slot Name'
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
    public function createBay()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/device-bay');

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
                        'parent_id' => 183,
                        'child_id' => 479,
                        'order' => 1,
                        'name' => 'Slot Name'
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
    public function updateBay()
    {
        $bayId = 4;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/device-bay/%s', [$bayId]));

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
                        'parent_id' => 183,
                        'child_id' => 479,
                        'order' => 1,
                        'name' => 'Slot Name Edit'
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
    public function deleteBay()
    {
        $bayId = 4;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/device-bay/%s', [$bayId]));

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
}