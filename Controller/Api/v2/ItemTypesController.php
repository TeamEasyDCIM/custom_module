<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class ItemTypesController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class ItemTypesController extends OutputController
{
    /**
     * @return string|void
     */
    public function createType()
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

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'data' => [
                        'name' => 'New Type',
                        'description' => 'Sample description',
                        'group' => 'software',
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
    public function updateType()
    {
        $typeId = 21;

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/type/%s', [$typeId]));

        /**
         * First Admin API Key - you should enter your own user api key
         */
        $apikey = \User::admins()->first()->apikey->key;

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->request('PUT', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'apikey' => $apikey
                ],
                'json' => [
                    'data' => [
                        'name' => 'New Type Edit',
                        'description' => 'Sample description edit',
                        'group' => 'software',
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