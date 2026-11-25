<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Controllers;

use App\BackendUi\Http\Controllers\V1\BackendUiController;
use App\BackendUi\Http\Responses\BackendUiResponse;
use App\BackendUi\Services\Extensions\ExtensionContent;
use App\BackendUi\Services\Modules\AddonPageFrames;
use App\Models\Device;
use App\Models\Item;
use Illuminate\Http\JsonResponse;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleSettings;
use Modules\Addons\CustomModule\Model\CustomModel;

/** The Overview page: counters, buttons and cards on the `addons-declarative-overview` template. */
final class OverviewController extends BackendUiController
{
    /** The API v3 endpoints of the module, as routes.php declares them. */
    private const API_ENDPOINTS = [
        'status' => ['GET', '/status'],
        'stats' => ['GET', '/stats'],
        'devices' => ['GET', '/devices'],
        'device' => ['GET', '/devices/{id}'],
        'metadata' => ['PATCH', '/devices/{id}/metadata'],
        'settings' => ['GET, PUT', '/settings'],
    ];

    /**
     * Read the Custom Module overview
     *
     * Returns the counters, buttons and cards of the Overview page. Requires the active web-admin session, the Custom Module permission and an active addon.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @param  CustomModuleAccess  $access  Module availability.
     * @param  CustomModuleSettings  $settings  Stored module settings.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success excerpt" {"success":true,"status":"success","message":"Success","data":{"notice":null,"stats":[],"actions":[],"cards":[]}}
     */
    public function show(CustomModuleAccess $access, CustomModuleSettings $settings): JsonResponse
    {
        $lang = CustomModuleAccess::LANG;
        $ready = $access->tableReady();
        $values = $settings->values();
        $api = '/api/v3/admin/'.CustomModuleAccess::SLUG;

        // ExtensionContent keeps plain text and checked actions only, so always answer through it.
        return BackendUiResponse::success(ExtensionContent::overview([
            'notice' => ['tone' => 'info', 'text' => trans($lang.'overview_notice')],
            'stats' => [
                ['key' => 'custom_items', 'label' => trans($lang.'custom_items'), 'value' => $ready ? CustomModel::query()->count() : 0, 'icon' => 'boxes'],
                ['key' => 'servers', 'label' => trans('backend/global.servers'), 'icon' => 'server',
                    'value' => Device::query()->where('items.type_id', item_type_id(Item::SERVER_TYPE_ID))->count()],
                ['key' => 'api_endpoints', 'label' => trans($lang.'card_api_endpoints'), 'value' => count(self::API_ENDPOINTS), 'sub' => $api, 'icon' => 'code'],
            ],
            'actions' => [
                ['key' => 'check', 'label' => trans($lang.'action_run_check'), 'kind' => 'api', 'method' => 'POST',
                    'url' => route(CustomModuleAccess::ROUTE.'.overview.check', [], false), 'variant' => 'primary'],
                ['key' => 'open_items', 'label' => trans($lang.'custom_items'), 'kind' => 'link',
                    'url' => route(AddonPageFrames::routeName(CustomModuleAccess::PAGE_ITEMS), [], false)],
                ['key' => 'open_settings', 'label' => trans($lang.'settings'), 'kind' => 'link',
                    'url' => route(AddonPageFrames::routeName(CustomModuleAccess::PAGE_SETTINGS), [], false)],
            ],
            'cards' => [
                [
                    'key' => 'module_status',
                    'title' => trans($lang.'card_module_status'),
                    'rows' => [
                        ['key' => 'table', 'label' => trans($lang.'row_database_table'),
                            'badge' => ['label' => trans($lang.($ready ? 'status_ready' : 'status_missing')), 'tone' => $ready ? 'success' : 'warning']],
                        ['key' => 'notifications', 'label' => trans($lang.'field_notifications_enabled'),
                            'value' => trans($values['notifications_enabled'] ? 'backend/global.yes' : 'backend/global.no')],
                        ['key' => 'log_level', 'label' => trans($lang.'field_log_level'), 'value' => trans($lang.'log_level_'.$values['log_level'])],
                    ],
                ],
                [
                    'key' => 'api_endpoints',
                    'title' => trans($lang.'card_api_endpoints'),
                    'description' => trans($lang.'card_api_endpoints_desc'),
                    'rows' => array_map(static fn (string $key, array $endpoint): array => ['key' => $key, 'label' => $endpoint[0], 'value' => $api.$endpoint[1]],
                        array_keys(self::API_ENDPOINTS), self::API_ENDPOINTS),
                ],
            ],
        ]));
    }

    /**
     * Run the sample check
     *
     * A sample button of the Overview page; it changes nothing. Requires the active web-admin session, the Custom Module permission and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"The check has passed.","data":{"checked_at":"2026-01-01T00:00:00+00:00"}}
     */
    public function check(): JsonResponse
    {
        return BackendUiResponse::success(['checked_at' => now()->toIso8601String()], trans(CustomModuleAccess::LANG.'overview_check_passed'));
    }
}
