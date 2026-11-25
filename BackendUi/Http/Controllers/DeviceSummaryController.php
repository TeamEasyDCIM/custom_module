<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Controllers;

use App\BackendUi\Http\Controllers\V1\BackendUiController;
use App\BackendUi\Http\Responses\BackendUiResponse;
use App\BackendUi\Services\Extensions\ExtensionContent;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\Model\CustomModel;

/**
 * The device Summary tab and the sample Actions menu entry.
 *
 * The permissions of a tab or an action only hide it. The routes of this controller require the device Summary permission as well.
 */
final class DeviceSummaryController extends BackendUiController
{
    private const ITEMS = 10;

    /**
     * Read the Custom Module tab of a device
     *
     * Returns the cards of the tab. Requires the active web-admin session, the Custom Module and device Summary permissions and an active addon.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @urlParam device integer required Device ID. Example: 1
     *
     * @param  int  $device  Device ID.
     * @param  CustomModuleAccess  $access  Module availability.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success excerpt" {"success":true,"status":"success","message":"Success","data":{"notice":null,"actions":[],"cards":[],"columns":[],"rows":[]}}
     * @response 404 scenario="Synthetic missing device" {"success":false,"status":"error","message":"Page Not Found","errors":{},"code":"not_found"}
     */
    public function tab(int $device, CustomModuleAccess $access): JsonResponse
    {
        $record = Device::query()->find($device);
        if ($record === null) {
            return $this->missing();
        }
        $lang = CustomModuleAccess::LANG;
        $items = $access->tableReady() ? CustomModel::query()->where('item_id', $device)->orderByDesc('id')->limit(self::ITEMS)->get() : collect();
        $rows = [];
        foreach ($items as $item) {
            $rows[] = ['key' => 'item_'.$item->getKey(), 'label' => '#'.$item->getKey().' '.$item->getRawOriginal('name'), 'value' => $item->getRawOriginal('type')];
        }

        // ExtensionContent keeps plain text and checked actions only, so always answer through it.
        return BackendUiResponse::success(ExtensionContent::tab([
            'notice' => ['tone' => 'info', 'text' => trans($lang.'device_tab_notice')],
            'cards' => [
                [
                    'key' => 'device',
                    'title' => trans($lang.'device_card_title'),
                    'rows' => [
                        ['key' => 'id', 'label' => trans('backend/global.id'), 'value' => $device],
                        ['key' => 'label', 'label' => trans('backend/global.label'), 'value' => $record->getRawOriginal('label')],
                    ],
                ],
                [
                    'key' => 'items',
                    'title' => trans($lang.'device_linked_items'),
                    'rows' => $rows !== [] ? $rows : [['key' => 'none', 'label' => trans($lang.'device_linked_items'), 'value' => trans($lang.'device_no_linked_items')]],
                    'actions' => [[
                        'key' => 'sync',
                        'label' => trans($lang.'action_sync'),
                        'kind' => 'api',
                        'method' => 'POST',
                        'url' => route(CustomModuleAccess::ROUTE.'.devices.sync', ['device' => $device], false),
                    ]],
                ],
            ],
        ]));
    }

    /**
     * Sync one device
     *
     * A sample Actions menu entry of the device Summary; it changes nothing. Requires the active web-admin session, the Custom Module and device Summary permissions and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @urlParam device integer required Device ID. Example: 1
     *
     * @param  int  $device  Device ID.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"Device #1 synchronized with Custom Module.","data":{"device_id":1}}
     * @response 404 scenario="Synthetic missing device" {"success":false,"status":"error","message":"Page Not Found","errors":{},"code":"not_found"}
     */
    public function sync(int $device): JsonResponse
    {
        if (! Device::query()->whereKey($device)->exists()) {
            return $this->missing();
        }

        return BackendUiResponse::success(['device_id' => $device], trans(CustomModuleAccess::LANG.'device_synced', ['id' => $device]));
    }

    private function missing(): JsonResponse
    {
        return BackendUiResponse::error(404, trans('backend/global.page_not_found'), 'not_found');
    }
}
