<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Http\Controllers;

use App\BackendUi\Http\Controllers\V1\Tables\AddonTableController;
use App\BackendUi\Http\Requests\V1\Tables\AddonTablePreferencesRequest;
use App\BackendUi\Http\Requests\V1\Tables\AddonTableRequest;
use App\BackendUi\Services\Tables\TableDefinitionRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** The four shared endpoints of every list of the module; the route names the list. */
final class TableController extends AddonTableController
{
    /**
     * Read a Custom Module list schema
     *
     * Returns the filter fields, columns, actions and page sizes of the list. Requires the active web-admin session, the Custom Module permission and an active addon.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @param  Request  $request  Current request.
     * @param  TableDefinitionRegistry  $tables  Table definition registry.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success excerpt" {"success":true,"status":"success","message":"Success","data":{"fields":[],"table":{}}}
     */
    public function schema(Request $request, TableDefinitionRegistry $tables): JsonResponse
    {
        return $this->schemaResponse($request, $tables);
    }

    /**
     * Read Custom Module list rows
     *
     * Returns one page of the list with the shared table search, filters and order. Requires the active web-admin session, the Custom Module permission and an active addon.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @param  AddonTableRequest  $request  Validated list state.
     * @param  TableDefinitionRegistry  $tables  Table definition registry.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success excerpt" {"success":true,"status":"success","message":"Success","data":[],"meta":{"statistics_supported":false}}
     */
    public function index(AddonTableRequest $request, TableDefinitionRegistry $tables): JsonResponse
    {
        return $this->rowsResponse($request, $tables);
    }

    /**
     * Read Custom Module list settings
     *
     * Returns the private column and table settings of the signed-in administrator. Requires the active web-admin session, the Custom Module permission and an active addon.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @param  Request  $request  Current request.
     * @param  TableDefinitionRegistry  $tables  Table definition registry.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success excerpt" {"success":true,"status":"success","message":"Success","data":{}}
     */
    public function preferences(Request $request, TableDefinitionRegistry $tables): JsonResponse
    {
        return $this->preferencesResponse($request, $tables);
    }

    /**
     * Save Custom Module list settings
     *
     * Stores one column and table settings snapshot for the signed-in administrator. Requires the active web-admin session, the Custom Module permission and an active addon; unsafe methods require web CSRF.
     *
     * @group Backend UI Endpoints
     *
     * @subgroup Custom Module
     *
     * @authenticated
     *
     * @bodyParam column_settings object required Column layout and profiles. No-example.
     * @bodyParam table_state object required Table view, page size and order. No-example.
     *
     * @param  AddonTablePreferencesRequest  $request  Validated settings.
     * @param  TableDefinitionRegistry  $tables  Table definition registry.
     * @return JsonResponse Backend UI response envelope.
     *
     * @response 200 scenario="Synthetic success excerpt" {"success":true,"status":"success","message":"Success","data":{}}
     */
    public function savePreferences(AddonTablePreferencesRequest $request, TableDefinitionRegistry $tables): JsonResponse
    {
        return $this->savePreferencesResponse($request, $tables);
    }
}
