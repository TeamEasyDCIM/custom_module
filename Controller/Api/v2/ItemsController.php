<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class ItemsController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class ItemsController extends OutputController
{
    /**
     * @return string|void
     */
    public function listItems()
    {
        /**
         * Endpoint URL
         */
        $url = url('api/v2/item');

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
                        'selfParent'
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
    public function showItem()
    {
        $itemId = 171;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/item/%s', [$itemId]));

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
}