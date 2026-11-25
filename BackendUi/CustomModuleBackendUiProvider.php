<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi;

use App\BackendUi\Events\Tables\BuildingTableActions;
use App\BackendUi\Events\Tables\BuildingTableColumns;
use App\BackendUi\Events\Tables\BuildingTableFilters;
use App\BackendUi\Http\Controllers\V1\Modules\AddonDeclarativePageController;
use App\BackendUi\Http\Middleware\BackendUiAccess;
use App\BackendUi\Http\Middleware\BackendUiActiveSession;
use App\BackendUi\Http\Middleware\BackendUiAllowedIps;
use App\BackendUi\Http\Middleware\BackendUiSecureConnection;
use App\BackendUi\Http\RoutePatterns;
use App\BackendUi\Localization\TranslationGroupRegistry;
use App\BackendUi\Services\Modules\AddonPageFrames;
use App\BackendUi\Services\Navigation\NavigationEntry;
use App\BackendUi\Services\Navigation\NavigationKind;
use App\BackendUi\Services\Navigation\NavigationModuleRegistrar;
use App\BackendUi\Services\Navigation\NavigationRegistry;
use App\BackendUi\Services\Pages\BackendUiPageCatalog;
use App\BackendUi\Services\Pages\BackendUiPageMount;
use App\BackendUi\Services\Summary\SummaryActionDefinition;
use App\BackendUi\Services\Summary\SummaryCardDefinition;
use App\BackendUi\Services\Summary\SummaryExtensionRegistry;
use App\BackendUi\Services\Summary\SummaryTabDefinition;
use App\BackendUi\Services\Tables\Actions\TableActionDefinition;
use App\BackendUi\Services\Tables\Addons\AddonTable;
use App\BackendUi\Services\Tables\Addons\AddonTableRoutes;
use App\BackendUi\Services\Tables\Columns\ColumnDefinition;
use App\BackendUi\Services\Tables\Filters\TableFilterDefinition;
use App\BackendUi\Services\Tables\TableDefinitionRegistry;
use Closure;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Modules\Addons\CustomModule\BackendUi\Extensions\DeviceSummaryExtensionRegistrar;
use Modules\Addons\CustomModule\BackendUi\Extensions\ServerTableExtensionListener;
use Modules\Addons\CustomModule\BackendUi\Services\CustomModuleAccess;
use Modules\Addons\CustomModule\BackendUi\Tables\CustomItemsTable;
use Modules\Addons\CustomModule\BackendUiNavigationServiceProvider;
use Throwable;

/**
 * Registers everything the module adds to the EasyDCIM 2.0 panel.
 *
 * Every step runs guarded: some core registries refuse a duplicate key with an exception, and an exception here would
 * stop the whole application (web, queue and command line), including the page that deactivates the module.
 */
final class CustomModuleBackendUiProvider extends ServiceProvider
{
    /** Declarative template of each page. */
    private const PAGES = [
        CustomModuleAccess::PAGE_OVERVIEW => 'addons-declarative-overview',
        CustomModuleAccess::PAGE_ITEMS => 'addons-declarative-list',
        CustomModuleAccess::PAGE_SETTINGS => 'addons-declarative-form',
    ];

    /** Every core class the routes and registrations of the module use (CustomModule20Test compares the list). */
    private const CORE_CLASSES = [
        BuildingTableActions::class,
        BuildingTableColumns::class,
        BuildingTableFilters::class,
        AddonDeclarativePageController::class,
        BackendUiAccess::class,
        BackendUiActiveSession::class,
        BackendUiAllowedIps::class,
        BackendUiSecureConnection::class,
        RoutePatterns::class,
        TranslationGroupRegistry::class,
        AddonPageFrames::class,
        NavigationEntry::class,
        NavigationKind::class,
        NavigationModuleRegistrar::class,
        NavigationRegistry::class,
        BackendUiPageCatalog::class,
        BackendUiPageMount::class,
        SummaryActionDefinition::class,
        SummaryCardDefinition::class,
        SummaryExtensionRegistry::class,
        SummaryTabDefinition::class,
        TableActionDefinition::class,
        AddonTable::class,
        AddonTableRoutes::class,
        ColumnDefinition::class,
        TableFilterDefinition::class,
        TableDefinitionRegistry::class,
    ];

    /** @var list<class-string> */
    private array $coreClasses = self::CORE_CLASSES;

    private bool $available = true;

    public function register(): void
    {
        // Before the routes: an error inside a route group would put every later route of EasyDCIM into that group.
        $missing = array_values(array_filter($this->coreClasses, static fn (string $class): bool => ! class_exists($class)));
        if ($missing !== []) {
            $this->available = false;
            Log::error('[CustomModule] The 2.0 panel is not available here. Custom Module 2.x requires EasyDCIM 2.0.', ['missing' => $missing]);

            return;
        }
        // Scoped: a table keeps the administrator and locale of one request.
        $this->app->scoped(CustomItemsTable::class);
        $this->loadRoutesFrom(__DIR__.'/../routes/backend-ui/custom-module.php');
    }

    public function boot(): void
    {
        if (! $this->available) {
            return;
        }
        $enabled = static fn (): bool => app(CustomModuleAccess::class)->available();

        $this->guarded('translations', function () use ($enabled): void {
            $this->app->make(TranslationGroupRegistry::class)->registerAddon(CustomModuleAccess::SLUG, 'backend', $enabled);
        });
        $this->guarded('Servers list', static function (): void {
            Event::listen(BuildingTableColumns::class, [ServerTableExtensionListener::class, 'columns']);
            Event::listen(BuildingTableActions::class, [ServerTableExtensionListener::class, 'actions']);
            Event::listen(BuildingTableFilters::class, [ServerTableExtensionListener::class, 'filters']);
        });
        $this->guarded('device Summary', function () use ($enabled): void {
            (new DeviceSummaryExtensionRegistrar)->register($this->app->make(SummaryExtensionRegistry::class), $enabled);
        });
        // The menu comes last: it is skipped when the pages could not be registered.
        $this->app->booted(fn () => $this->guarded('pages', fn () => $this->registerPages($enabled)));
    }

    /** @param  Closure(): bool  $enabled */
    private function registerPages(Closure $enabled): void
    {
        $catalog = $this->app->make(BackendUiPageCatalog::class);
        foreach (self::PAGES as $page => $template) {
            $catalog->register(
                id: $page,
                route: AddonPageFrames::routeName($page),
                layout: 'private',
                permissions: ['core', CustomModuleAccess::PERMISSION],
                postLogin: true,
                admission: $enabled,
                template: $template,
            );
        }
        $this->app->make(TableDefinitionRegistry::class)->register(CustomItemsTable::IDENTITY, CustomItemsTable::class);
        $this->app->register(BackendUiNavigationServiceProvider::class);
    }

    /** Runs one registration step; a failing one is logged and leaves the rest of the application working. */
    private function guarded(string $step, Closure $register): void
    {
        try {
            $register();
        } catch (Throwable $exception) {
            Log::error('[CustomModule] The 2.0 panel registration failed.', [
                'step' => $step,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
