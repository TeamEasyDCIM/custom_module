<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Controllers;

use App\BackendUi\Http\Controllers\V1\BackendUiController;
use App\BackendUi\Http\Responses\BackendUiResponse;
use App\BackendUi\Services\Tables\MassSelection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\Model\CustomModel;

/** The row and bulk actions of the Custom Items list. */
final class CustomItemsActionController extends BackendUiController
{
    public function __construct(private readonly CustomModuleAccess $access) {}

    /**
     * Sync one custom item
     *
     * A sample row action that updates the item date. Requires the active web-admin session, the Custom Module permission and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @urlParam item integer required Custom item ID. Example: 1
     *
     * @param  int  $item  Custom item ID.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"Custom item #1 synchronized.","data":{"id":1}}
     * @response 404 scenario="Synthetic missing item" {"success":false,"status":"error","message":"Page Not Found","errors":{},"code":"not_found"}
     */
    public function sync(int $item): JsonResponse
    {
        $record = $this->find($item);
        if ($record === null) {
            return $this->missing();
        }
        $record->setUpdatedAt($record->freshTimestamp())->save();

        return BackendUiResponse::success(['id' => $item], trans(CustomModuleAccess::LANG.'item_synced', ['id' => $item]));
    }

    /**
     * Delete one custom item
     *
     * Requires the active web-admin session, the Custom Module permission and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @urlParam item integer required Custom item ID. Example: 1
     *
     * @param  int  $item  Custom item ID.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"Custom item deleted.","data":{"id":1}}
     * @response 404 scenario="Synthetic missing item" {"success":false,"status":"error","message":"Page Not Found","errors":{},"code":"not_found"}
     */
    public function destroy(int $item): JsonResponse
    {
        $record = $this->find($item);
        if ($record === null) {
            return $this->missing();
        }
        $record->delete();

        return BackendUiResponse::success(['id' => $item], trans(CustomModuleAccess::LANG.'item_deleted'));
    }

    /**
     * Delete the selected custom items
     *
     * Requires the active web-admin session, the Custom Module permission and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @bodyParam ids integer[] required Custom item IDs, at most the mass action limit of EasyDCIM. No-example.
     *
     * @param  Request  $request  Current request.
     * @param  MassSelection  $selection  Mass action limit of EasyDCIM.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success" {"success":true,"status":"success","message":"2 custom item(s) deleted.","data":{"deleted":2}}
     */
    public function massDelete(Request $request, MassSelection $selection): JsonResponse
    {
        $ids = $request->validate(['ids' => ['required', 'array', 'list', 'min:1', 'max:'.$selection->maximum()], 'ids.*' => ['required', 'integer', 'min:1', 'distinct']])['ids'];
        if (! $this->access->tableReady()) {
            return $this->missing();
        }
        $deleted = CustomModel::query()->whereIn('id', $ids)->delete();

        return BackendUiResponse::success(['deleted' => $deleted], trans(CustomModuleAccess::LANG.'items_deleted', ['count' => $deleted]));
    }

    private function find(int $id): ?CustomModel
    {
        return $this->access->tableReady() ? CustomModel::query()->find($id) : null;
    }

    private function missing(): JsonResponse
    {
        return BackendUiResponse::error(404, trans('backend/global.page_not_found'), 'not_found');
    }
}
