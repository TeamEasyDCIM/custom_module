<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule\BackendUi\Services;

use Components\Modules\Core\AddonModule;
use Illuminate\Support\Facades\Schema;

/**
 * The identity and access rules of the module.
 *
 * Every permission, page, route, table and extension key derives from `SLUG` and `KEY`, so a copy of this module
 * that changes these two (and `PROPER_NAME`) shares no key with the original.
 */
final class CustomModuleAccess
{
    /** Directory and class name of the module. */
    public const PROPER_NAME = 'CustomModule';

    /** The module in addresses, permissions and translations. */
    public const SLUG = 'custom-module';

    /** The module in keys that allow no hyphen (columns, filters, actions, tabs, options). */
    public const KEY = 'custom_module';

    /** Permission of every page, endpoint and extension of the module. */
    public const PERMISSION = 'core.modules.addons.'.self::SLUG;

    /** Core permissions of the pages the module extends; its endpoints check them as well. */
    public const SERVERS_PERMISSION = 'core.devices.servers';

    public const SUMMARY_PERMISSION = 'core.devices.base.summary';

    /** Prefix of the translation keys. */
    public const LANG = self::SLUG.'::backend.';

    /** Address prefix and route name prefix of the private endpoints. */
    public const API = '/backend/api/v1/'.self::SLUG;

    public const ROUTE = 'backend.api.v1.'.self::SLUG;

    /** Database table and option key prefix; both hold stored data, so they stay as they are in 1.x. */
    public const TABLE = 'custom_module_table';

    public const OPTIONS = 'custom_module.';

    public const PAGE_OVERVIEW = self::SLUG.'-overview';

    public const PAGE_ITEMS = self::SLUG.'-items';

    public const PAGE_SETTINGS = self::SLUG.'-settings';

    /** @var array<string, array{page: string, label: string}> Tabs above every page of the module. */
    public const TABS = [
        'overview' => ['page' => self::PAGE_OVERVIEW, 'label' => self::LANG.'overview'],
        'items' => ['page' => self::PAGE_ITEMS, 'label' => self::LANG.'custom_items'],
        'settings' => ['page' => self::PAGE_SETTINGS, 'label' => self::LANG.'settings'],
    ];

    /** Whether the module is enabled, activated and licensed. Asked on every request, so never cache the answer. */
    public function available(): bool
    {
        if (! app()->bound('ModuleRepository')) {
            return false;
        }
        $repository = app('ModuleRepository');
        if (! $repository->isEnabled(self::PROPER_NAME)) {
            return false;
        }
        $module = $repository->getByProperName(self::PROPER_NAME);

        return $module instanceof AddonModule && $module->isActivated() && ($module->isFree() || $module->isBought());
    }

    /** Whether the migration of the module has run. */
    public function tableReady(): bool
    {
        return Schema::hasTable(self::TABLE);
    }
}
