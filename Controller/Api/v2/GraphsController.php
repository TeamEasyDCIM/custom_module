<?php

namespace Modules\Addons\CustomModule\Controller\Api\v2;
use Modules\Addons\CustomModule\Controller\OutputController;

/**
 * Class GraphsController
 * @package Modules\Addons\CustomModule\Controller\Api\v2
 */
class GraphsController extends OutputController
{
    /**
     * @return string|void
     */
    public function renderGraph()
    {
        $targetId = 163;
        $targetType = 'device';

        /**
         * Endpoint URL
         */
        $url = url(vsprintf('api/v2/graphs/%s/%s', [$targetId, $targetType]));

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
                    'type' => 'Ping',
                    'width' => 800,
                    'height' => 300,
                    // 'legend' => 'no',
                    'start' => 1559202741,
                    'end' => 1559289141
                ]
            ]);

            $stream = \GuzzleHttp\Psr7\stream_for($response->getBody());

            $result = json_decode($stream->getContents());

            echo vsprintf('<img src="data:image/png;base64, %s" />', [$result->result]);
            die();
        } catch (\Exception $e) {
            d($e->getMessage());
        }
    }
}