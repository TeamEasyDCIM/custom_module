<?php

declare(strict_types=1);

/** The 2.0 panel pages and private endpoints of the module. */

use App\BackendUi\Http\Controllers\V1\Modules\AddonDeclarativePageController;
use App\BackendUi\Http\Middleware\BackendUiAccess;
use App\BackendUi\Http\Middleware\BackendUiActiveSession;
use App\BackendUi\Http\Middleware\BackendUiAllowedIps;
use App\BackendUi\Http\Middleware\BackendUiSecureConnection;
use App\BackendUi\Http\RoutePatterns;
use App\BackendUi\Services\Pages\BackendUiPageMount;
use App\BackendUi\Services\Tables\Addons\AddonTableRoutes;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\AllowedIps;
use App\Http\Middleware\ForceSecuredMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\Addons\CustomModule\BackendUi\Http\Controllers\CustomItemsActionController;
use Modules\Addons\CustomModule\BackendUi\Http\Controllers\DeviceSummaryController;
use Modules\Addons\CustomModule\BackendUi\Http\Controllers\HeaderController;
use Modules\Addons\CustomModule\BackendUi\Http\Controllers\OverviewController;
use Modules\Addons\CustomModule\BackendUi\Http\Controllers\ServerSyncController;
use Modules\Addons\CustomModule\BackendUi\Http\Controllers\SettingsController;
use Modules\Addons\CustomModule\BackendUi\Http\Controllers\TableController;
use Modules\Addons\CustomModule\BackendUi\Http\Middleware\CustomModuleAvailable;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\BackendUi\Tables\CustomItemsTable;

// Every 2.0 route of an addon has the same checks: session, two-factor, core access, its own permission, active module.
$middleware = [
    'web',
    BackendUiSecureConnection::class,
    BackendUiAllowedIps::class,
    BackendUiActiveSession::class,
    BackendUiAccess::class.':core',
    BackendUiAccess::class.':'.CustomModuleAccess::PERMISSION,
    CustomModuleAvailable::class,
];

// The checks above answer for the 2.0 panel; these redirect to the 1.x login.
$without = [
    AdminAuth::class,
    AllowedIps::class,
    ForceSecuredMiddleware::class,
    'Modules\\Addons\\EnterpriseIdentity\\Http\\Middleware\\EnsureBackendUserActive',
    'Modules\\Addons\\TwoFactorAuth\\Http\\Middleware\\TwoFactorMiddleware',
];

// Whatever can fail is evaluated outside the groups.
$pages = BackendUiPageMount::prefix();
$id = RoutePatterns::ID;
$tables = [CustomItemsTable::class => 'items'];
$throttle = static fn (string $name): string => 'backend-ui.throttle:30,1,backend-ui.'.CustomModuleAccess::SLUG.'.'.$name.'.';

// Laravel keeps a group open when its routes throw, and every later route of EasyDCIM would join it. So a failure leaves
// each group the normal way and is thrown after the groups.
$failure = null;
$safely = static function (Closure $routes) use (&$failure): Closure {
    return static function () use ($routes, &$failure): void {
        try {
            $routes();
        } catch (Throwable $exception) {
            $failure ??= $exception;
        }
    };
};

// Pages. Each one is also registered in CustomModuleBackendUiProvider with its template.
Route::middleware($middleware)->withoutMiddleware($without)->prefix($pages)->group($safely(static function (): void {
    $header = CustomModuleAccess::ROUTE.'.header';
    AddonDeclarativePageController::overviewRoute(CustomModuleAccess::SLUG.'/overview', CustomModuleAccess::PAGE_OVERVIEW, CustomModuleAccess::PROPER_NAME,
        $header, CustomModuleAccess::ROUTE.'.overview.show', CustomModuleAccess::LANG.'overview');
    AddonDeclarativePageController::listRoute(CustomModuleAccess::SLUG.'/items', CustomModuleAccess::PAGE_ITEMS, CustomModuleAccess::PROPER_NAME,
        $header, CustomModuleAccess::ROUTE.'.items', CustomModuleAccess::LANG.'custom_items');
    AddonDeclarativePageController::formRoute(CustomModuleAccess::SLUG.'/settings', CustomModuleAccess::PAGE_SETTINGS, CustomModuleAccess::PROPER_NAME,
        $header, CustomModuleAccess::ROUTE.'.settings.show', CustomModuleAccess::LANG.'settings');
}));

// Endpoints. Every writing one has its own throttle.
$endpoints = static fn (string ...$more) => Route::middleware([...$middleware, ...$more])->withoutMiddleware($without)->prefix(ltrim(CustomModuleAccess::API, '/'))->name(CustomModuleAccess::ROUTE.'.');

$endpoints()->group($safely(static function () use ($id, $tables, $throttle): void {
    Route::get('header', [HeaderController::class, 'show'])->name('header');
    Route::get('overview', [OverviewController::class, 'show'])->name('overview.show');
    Route::post('overview/check', [OverviewController::class, 'check'])->middleware($throttle('overview.check'))->name('overview.check');
    Route::get('settings', [SettingsController::class, 'show'])->name('settings.show');
    Route::patch('settings', [SettingsController::class, 'update'])->middleware($throttle('settings.update'))->name('settings.update');

    AddonTableRoutes::register(TableController::class, $tables, CustomModuleAccess::SLUG);
    Route::post('items/mass-delete', [CustomItemsActionController::class, 'massDelete'])->middleware($throttle('items.mass-delete'))->name('items.mass-delete');
    Route::post('items/{item}/sync', [CustomItemsActionController::class, 'sync'])->where('item', $id)->middleware($throttle('items.sync'))->name('items.sync');
    Route::delete('items/{item}', [CustomItemsActionController::class, 'destroy'])->where('item', $id)->middleware($throttle('items.destroy'))->name('items.destroy');
}));

// The permissions of an action or a tab only hide it, so an endpoint behind a core page checks the permission of that page itself.
$endpoints(BackendUiAccess::class.':'.CustomModuleAccess::SERVERS_PERMISSION)->group($safely(static function () use ($id, $throttle): void {
    Route::post('servers/sync', [ServerSyncController::class, 'selected'])->middleware($throttle('servers.sync-selected'))->name('servers.sync-selected');
    Route::post('servers/{server}/sync', [ServerSyncController::class, 'one'])->where('server', $id)->middleware($throttle('servers.sync'))->name('servers.sync');
}));
$endpoints(BackendUiAccess::class.':'.CustomModuleAccess::SUMMARY_PERMISSION)->group($safely(static function () use ($id, $throttle): void {
    Route::get('devices/{device}/tab', [DeviceSummaryController::class, 'tab'])->where('device', $id)->name('devices.tab');
    Route::post('devices/{device}/sync', [DeviceSummaryController::class, 'sync'])->where('device', $id)->middleware($throttle('devices.sync'))->name('devices.sync');
}));

if ($failure !== null) {
    throw $failure;
}
