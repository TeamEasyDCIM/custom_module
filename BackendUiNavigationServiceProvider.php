<?php

namespace Modules\Addons\CustomModule;

use App\BackendUi\Services\Navigation\NavigationModuleRegistrar;
use Illuminate\Support\ServiceProvider;

/** Only new UI menu declarations; legacy routes/events/menu remain independent. */
final class BackendUiNavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->make(NavigationModuleRegistrar::class)->register('custom-module', 'CustomModule');
        $this->app->make(\App\BackendUi\Services\Navigation\NavigationRegistry::class)->register(
            new \App\BackendUi\Services\Navigation\NavigationEntry(
                'devices.custom_page', \App\BackendUi\Services\Navigation\NavigationKind::Page,
                parent: 'devices', order: 70, label: 'Custom Page',
                visible: NavigationModuleRegistrar::visibility('CustomModule'),
            ),
        );
    }
}
