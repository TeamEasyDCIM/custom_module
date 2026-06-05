<?php

namespace Modules\Addons\CustomModule;

use Illuminate\Support\Facades\Artisan;
use Modules\Addons\CustomModule\Support\Installers\CustomModelTableInstaller;
use Components\Modules\Core\AddonModule;
use Components\Modules\Interfaces\ModuleInterface;

/**
 * Class CustomModule
 * @package Modules\Addons\CustomModule
 */
class CustomModule extends AddonModule implements ModuleInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Custom Module';
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return 'custom-module';
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Custom Module Description';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0';
    }

    /**
     * @return bool
     */
    public function isFree()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getDocumentation()
    {
        return 'https://www.easydcim.com/documentation/extensions-integrations/extensions-development/sample-extension/';
    }

    /**
     * Called when the module is activated in the admin panel (Modules → My Modules).
     *
     * Use this to set up anything the module needs to start working:
     *   - write default settings to the options table via OptionRepository
     *   - run module migrations and seed demo data
     *   - register scheduled tasks
     *
     * Example:
     *   app('OptionRepository')->add('custom_module.enabled', true);
     *
     * @return void
     */
    public function onActivate()
    {
        Artisan::call('migrate', [
            '--path' => 'modules/addons/CustomModule/database/migrations',
            '--force' => true,
        ]);

        app(CustomModelTableInstaller::class)->install();
    }

    /**
     * Called when the module is deactivated in the admin panel.
     *
     * Use this to clean up resources the module added:
     *   - remove settings from the options table
     *   - drop module-specific database tables (only if safe to discard data)
     *   - cancel any scheduled tasks registered by this module
     *
     * Example:
     *   app('OptionRepository')->delete('custom_module.enabled');
     *
     * @return void
     */
    public function onDeactivate() {}
}