<?php

namespace Modules\Addons\CustomModule\Controller\Api\v3;

use App\Models\Device;
use App\Models\Service;
use App\Repositories\DevicesRepository;
use Components\API\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Example G: EasyDCIM API v3 endpoints for a module.
 *
 * API v2 examples were removed from CustomModule because API v2 is no longer
 * supported. API v3 uses Laravel Sanctum bearer tokens and returns responses
 * through Components\API\Helpers\ApiResponse.
 *
 * Routes are defined in modules/addons/CustomModule/routes.php:
 *   /api/v3/admin/custom-module/*
 *
 * Required headers:
 *   Authorization: Bearer <sanctum-token>
 *   Accept: application/json
 */
class ExampleController
{
    private const OPTION_PREFIX = 'custom_module.';

    /** The only device metadata this API reads and writes. */
    public const METADATA_LABELS = ['Custom Module Note', 'Custom Module Enabled'];

    public function status(): JsonResponse
    {
        return ApiResponse::success(data: [
            'module' => 'CustomModule',
            'api_version' => 'v3',
            'auth' => 'sanctum',
            'status' => 'active',
        ]);
    }

    /**
     * GET /api/v3/admin/custom-module/devices?per_page=25
     */
    public function devices(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $devices = Device::query()
            ->with(['type', 'itemModel'])
            ->orderByDesc('id')
            ->paginate($perPage);

        return ApiResponse::success(data: [
            'items' => collect($devices->items())->map(fn (Device $device) => $this->devicePayload($device))->values(),
            'pagination' => [
                'total' => $devices->total(),
                'per_page' => $devices->perPage(),
                'current_page' => $devices->currentPage(),
                'last_page' => $devices->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/v3/admin/custom-module/devices/{id}
     */
    public function showDevice(int $id, DevicesRepository $devices): JsonResponse
    {
        $device = $devices->fetchById($id, ['type', 'itemModel', 'metadata']);

        return ApiResponse::success(data: $this->devicePayload($device, includeMetadata: true));
    }

    /**
     * PATCH /api/v3/admin/custom-module/devices/{id}/metadata
     *
     * Accepts the labels of METADATA_LABELS only.
     *
     * Example JSON body:
     * {
     *   "metadata": {
     *     "Custom Module Note": "Created by API v3 example",
     *     "Custom Module Enabled": "yes"
     *   }
     * }
     */
    public function updateDeviceMetadata(int $id, Request $request, DevicesRepository $devices): JsonResponse
    {
        $validated = $request->validate([
            // Only these keys: any other label or field ID could overwrite a credential or a system field.
            'metadata' => ['required', 'array:' . implode(',', self::METADATA_LABELS)],
            'metadata.*' => ['nullable', 'string', 'max:1000'],
        ]);

        $device = $devices->fetchById($id, ['metadata']);
        $device->forceAssignMetadata($validated['metadata']);

        return ApiResponse::success(
            message: 'Device metadata updated.',
            data: $this->devicePayload($device->refresh(), includeMetadata: true)
        );
    }

    /**
     * GET /api/v3/admin/custom-module/stats
     */
    public function stats(): JsonResponse
    {
        return ApiResponse::success(data: [
            'total_devices' => Device::count(),
            'total_services' => Service::count(),
            'active_services' => Service::query()->where('status', 'active')->count(),
        ]);
    }

    /**
     * GET /api/v3/admin/custom-module/settings
     */
    public function settings(): JsonResponse
    {
        $options = app('OptionRepository');
        return ApiResponse::success(data: $this->settingsPayload($options));
    }

    /**
     * PUT /api/v3/admin/custom-module/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $options = app('OptionRepository');
        $validated = $request->validate([
            'notifications_enabled' => ['sometimes', 'boolean'],
            'notification_email' => ['nullable', 'email', 'max:255'],
            'log_level' => ['sometimes', 'in:debug,info,warning,error'],
        ]);

        foreach ($validated as $key => $value) {
            $options->add(self::OPTION_PREFIX . $key, $value);
        }

        return ApiResponse::success(
            message: 'CustomModule settings saved.',
            data: $this->settingsPayload($options)
        );
    }

    private function devicePayload(Device $device, bool $includeMetadata = false): array
    {
        $payload = [
            'id' => $device->id,
            'label' => $device->label,
            'status' => $device->status,
            'type' => optional($device->type)->name,
            'model' => optional($device->itemModel)->name,
        ];

        if ($includeMetadata) {
            $payload['metadata'] = $this->metadataPayload($device);
        }

        return $payload;
    }

    /**
     * The metadata of this module only. The other fields of a device hold credentials (IPMI, SNMP, root password).
     *
     * @return array<string, mixed>
     */
    private function metadataPayload(Device $device): array
    {
        $list = [];
        foreach ($device->getAttribute('metadata') as $value) {
            $label = (string) $value->getAttribute('fieldLabel');
            if (in_array($label, self::METADATA_LABELS, true) && (int) $value->getAttribute('encrypted') === 0) {
                $list[$label] = $value->getAttribute('value');
            }
        }

        return $list;
    }

    private function settingsPayload($options): array
    {
        return [
            'notifications_enabled' => (bool) $options->get(self::OPTION_PREFIX . 'notifications_enabled'),
            'notification_email' => $options->get(self::OPTION_PREFIX . 'notification_email') ?: '',
            'log_level' => $options->get(self::OPTION_PREFIX . 'log_level') ?: 'info',
        ];
    }
}
