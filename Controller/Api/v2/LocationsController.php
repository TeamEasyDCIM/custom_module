<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class LocationsController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class LocationsController extends OutputController
{
    /**
     * @return string|void
     */
    public function createLocation()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/location');

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
                        'name' => 'New York',
                        'address' => 'York Street Subway Station',
                        'lat' => '40.7013482',
                        'lng' => '-73.9866001'
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
    public function createFloor()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/floor');

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
                        'name' => 'Test Floor 1',
                        'number' => 2,
                        'location_id' => 1,
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
    public function createRack()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/rack');

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
                        'name' => 'Test Rack 1',
                        'floor_id' => 4,
                        'location_id' => 1,
                        'units_number' => 10
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