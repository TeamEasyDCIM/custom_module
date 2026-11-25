<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule;

use App\BackendUi\Services\Navigation\NavigationEntry;
use App\BackendUi\Services\Navigation\NavigationKind;
use App\BackendUi\Services\Navigation\NavigationModuleRegistrar;
use App\BackendUi\Services\Navigation\NavigationRegistry;
use Illuminate\Support\ServiceProvider;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;

/**
 * Menu entries of the module. Registered after its pages (see CustomModuleBackendUiProvider): EasyDCIM leaves out and
 * logs an entry whose page is missing.
 */
final class BackendUiNavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // System > Extensions > Installed Extensions, opening the Overview page.
        $this->app->make(NavigationModuleRegistrar::class)->register(
            slug: CustomModuleAccess::SLUG,
            properName: CustomModuleAccess::PROPER_NAME,
            page: CustomModuleAccess::PAGE_OVERVIEW,
        );

        // A page inside the Devices group of the menu.
        $this->app->make(NavigationRegistry::class)->register(new NavigationEntry(
            id: 'devices.'.CustomModuleAccess::KEY,
            kind: NavigationKind::Page,
            translationKey: CustomModuleAccess::LANG.'custom_items',
            parent: 'devices',
            order: 70,
            permissions: [CustomModuleAccess::PERMISSION],
            page: CustomModuleAccess::PAGE_ITEMS,
            icon: 'puzzle',
            visible: NavigationModuleRegistrar::visibility(CustomModuleAccess::PROPER_NAME),
        ));
    }
}
