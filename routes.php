<?php

declare(strict_types=1);

/**
 * Sanctum Admin API v3 routes exposed by Custom Module.
 *
 * Backend UI 2.0 panel pages and `/backend/api/v1/custom-module/*` endpoints live
 * in `routes/backend-ui/custom-module.php`.
 */

use Illuminate\Support\Facades\Route;
use Modules\Addons\CustomModule\Controller\Api\v3\ExampleController;

Route::middleware(['auth:sanctum', 'api', 'acl:core.modules.addons.custom-module'])
    ->prefix('api/v3/admin/custom-module')
    ->name('api.v3.admin.custom-module.')
    ->group(function (): void {
        Route::get('/status', [ExampleController::class, 'status'])->name('status');
        Route::get('/settings', [ExampleController::class, 'settings'])->name('settings.show');
        Route::put('/settings', [ExampleController::class, 'updateSettings'])->name('settings.update');

        // Core data needs the permission of the core endpoint as well; the permission of the module alone never opens it.
        Route::middleware('acl:api_v3.admin.devices.read')->group(function (): void {
            Route::get('/stats', [ExampleController::class, 'stats'])->name('stats');
            Route::get('/devices', [ExampleController::class, 'devices'])->name('devices.index');
            Route::get('/devices/{id}', [ExampleController::class, 'showDevice'])->name('devices.show');
        });
        Route::patch('/devices/{id}/metadata', [ExampleController::class, 'updateDeviceMetadata'])
            ->name('devices.metadata.update')->middleware('acl:api_v3.admin.devices.metadata_assign');
    });
