<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Controllers;

use App\BackendUi\Http\Controllers\V1\BackendUiController;
use App\BackendUi\Http\Responses\BackendUiResponse;
use App\BackendUi\Services\Tables\MassSelection;
use App\Models\Device;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;

/**
 * The sample actions of the Servers list; they change nothing.
 *
 * The permissions of an action only hide its button. The routes of this controller require the Servers permission as well.
 */
final class ServerSyncController extends BackendUiController
{
    /**
     * Sync one server
     *
     * A sample row action of the Servers list. Requires the active web-admin session, the Custom Module and Servers permissions and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @urlParam server integer required Server ID. Example: 1
     *
     * @param  int  $server  Server ID.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"Server #1 synchronized with Custom Module.","data":{"server_id":1}}
     * @response 404 scenario="Synthetic missing server" {"success":false,"status":"error","message":"Page Not Found","errors":{},"code":"not_found"}
     */
    public function one(int $server): JsonResponse
    {
        if (! $this->servers()->whereKey($server)->exists()) {
            return BackendUiResponse::error(404, trans('backend/global.page_not_found'), 'not_found');
        }

        return BackendUiResponse::success(['server_id' => $server], trans(CustomModuleAccess::LANG.'server_synced', ['id' => $server]));
    }

    /**
     * Sync the selected servers
     *
     * A sample bulk action of the Servers list. Requires the active web-admin session, the Custom Module and Servers permissions and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @bodyParam ids integer[] required Server IDs, at most the mass action limit of EasyDCIM. No-example.
     *
     * @param  Request  $request  Current request.
     * @param  MassSelection  $selection  Mass action limit of EasyDCIM.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"2 server(s) synchronized with Custom Module.","data":{"synced":2}}
     */
    public function selected(Request $request, MassSelection $selection): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array', 'list', 'min:1', 'max:'.$selection->maximum()], 'ids.*' => ['required', 'integer', 'min:1', 'distinct']])['ids'];
        $count = $this->servers()->whereKey($ids)->count();

        return BackendUiResponse::success(['synced' => $count], trans(CustomModuleAccess::LANG.'servers_synced', ['count' => $count]));
    }

    /** The same set as the Servers list; an ID of another device type is not a server. */
    private function servers(): Builder
    {
        return Device::query()->where('items.type_id', item_type_id(Item::SERVER_TYPE_ID));
    }
}
