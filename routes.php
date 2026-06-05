<?php

use Illuminate\Support\Facades\Route;
use Modules\Addons\CustomModule\Controller\PageController;
use Modules\Addons\CustomModule\Controller\DeviceController;
use Modules\Addons\CustomModule\Controller\CustomModelController;
use Modules\Addons\CustomModule\Controller\SettingsController;
use Modules\Addons\CustomModule\Controller\Api\v3\ExampleController;

Route::middleware(['web', 'acl:core.modules.addons.custom-module'])
    ->prefix('backend')
    ->namespace('Modules\Addons\CustomModule\Controller')
    ->group(function () {

    /** ---------------------- PAGE ROUTES ---------------------- */
    Route::get('/custom-module/tab1', [PageController::class, 'showTab1'])
        ->name('backend.custom.module.tab1');

    Route::get('/custom-module/tab1/{id}/action/enable', [PageController::class, 'enableAction'])
        ->name('backend.custom.module.tab1.action.enable');

    Route::get('/custom-module/tab1/{id}/action/disable', [PageController::class, 'disableAction'])
        ->name('backend.custom.module.tab1.action.disable');

    Route::post('/custom-module/tab1/edit/quick', [PageController::class, 'quickEdit'])
        ->name('backend.custom.module.tab1.edit.quick');

    /** ---------------------- API v3 EXAMPLES PAGE ---------------------- */
    Route::get('/custom-module/api', [PageController::class, 'apiRequest'])
        ->name('backend.custom.module.tab2');

    /** ---------------------- SETTINGS PAGE ---------------------- */
    Route::get('/custom-module/settings', [SettingsController::class, 'showSettings'])
        ->name('backend.custom.module.settings');

    Route::post('/custom-module/settings/save', [SettingsController::class, 'saveSettings'])
        ->name('backend.custom.module.settings.save');

    /** ---------------------- DEVICE ROUTES ---------------------- */
    Route::get('/custom-module/devices/{id}/custom-tab', [DeviceController::class, 'showCustomTab'])
        ->name('backend.custom.module.device.tab');

    /** ---------------------- CUSTOM MODEL TABLE ROUTES ---------------------- */
    Route::get('/custom-module/custom-model-table', [CustomModelController::class, 'index'])
        ->name('backend.custom.module.tab3');

    Route::get('/custom-module/custom-model-table/filter={filters?}{output?}', [CustomModelController::class, 'index'])
        ->name('backend.custom.module.tab3.filtered')
        ->where(['filters' => '(.*)']);
});

Route::middleware(['auth:sanctum', 'api', 'acl:core.modules.addons.custom-module'])
    ->prefix('api/v3/admin/custom-module')
    ->name('api.v3.admin.custom-module.')
    ->group(function () {
        Route::get('/status', [ExampleController::class, 'status'])->name('status');
        Route::get('/stats', [ExampleController::class, 'stats'])->name('stats');
        Route::get('/devices', [ExampleController::class, 'devices'])->name('devices.index');
        Route::get('/devices/{id}', [ExampleController::class, 'showDevice'])->name('devices.show');
        Route::patch('/devices/{id}/metadata', [ExampleController::class, 'updateDeviceMetadata'])->name('devices.metadata.update');
        Route::get('/settings', [ExampleController::class, 'settings'])->name('settings.show');
        Route::put('/settings', [ExampleController::class, 'updateSettings'])->name('settings.update');
    });
