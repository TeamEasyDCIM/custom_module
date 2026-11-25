<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\Controller;

use App\BackendUi\Http\Middleware\BackendUiAccess;
use Components\Modules\Http\Controllers\ModuleController;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;

/** The address the 1.x panel links a module to (My Modules, breadcrumbs): the 2.0 Overview page, or My Modules without the 2.0 panel. */
class OutputController extends ModuleController
{
    public function getIndex(): RedirectResponse
    {
        $page = 'backend.ui.'.CustomModuleAccess::PAGE_OVERVIEW;
        // The routes of the 2.0 panel exist also where the panel is off and answers 404. EasyDCIM 1.x has no such check.
        $panel = is_callable([BackendUiAccess::class, 'panelEnabled']) && BackendUiAccess::panelEnabled() && Route::has($page);

        return redirect()->to(route($panel ? $page : 'backend.modules.addons', [], false));
    }
}
