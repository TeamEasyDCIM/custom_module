<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Controllers;

use App\BackendUi\Http\Controllers\V1\BackendUiController;
use App\BackendUi\Http\Responses\BackendUiResponse;
use App\BackendUi\Localization\UiText;
use App\BackendUi\Services\Modules\AddonPageFrames;
use Illuminate\Http\JsonResponse;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;

/** The tabs above every page of the module. */
final class HeaderController extends BackendUiController
{
    /**
     * Read the Custom Module tabs
     *
     * Returns the tabs of the module in their order. Requires the active web-admin session, the Custom Module permission and an active addon.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"Success","data":{"tabs":[{"key":"overview","label":"Overview","legacy":false,"url":"/admin/custom-module/overview"}]}}
     */
    public function show(): JsonResponse
    {
        $tabs = [];
        foreach (CustomModuleAccess::TABS as $key => $tab) {
            $tabs[] = [
                'key' => $key,
                'label' => UiText::get($tab['label']),
                'legacy' => false,
                'url' => route(AddonPageFrames::routeName($tab['page']), [], false),
            ];
        }

        return BackendUiResponse::success(['tabs' => $tabs]);
    }
}
