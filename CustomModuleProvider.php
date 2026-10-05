<?php

namespace Modules\Addons\CustomModule;

use App\Models\Device;
use App\Providers\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Addons\CustomModule\Commands\CustomModuleCommand;
use Modules\Addons\CustomModule\Commands\IpmiBmcResetCommand;
use Modules\Addons\CustomModule\Observers\DeviceObserver;

/**
 * Class CustomModuleProvider
 *
 * Keep the provider small: it should only bootstrap services owned by the module.
 * Event listeners live in CustomModuleEventHandler and model hooks live in observers.
 * This follows the same pattern as other EasyDCIM addons, where ModuleRepository
 * automatically subscribes a {ModuleName}EventHandler class when it exists.
 *
 * @package Modules\Addons\CustomModule
 */
class CustomModuleProvider extends ModuleServiceProvider
{
    /**
     * Register scheduled tasks for this module.
     *
     * @return void
     */
    public function boot(Schedule $schedule)
    {
        parent::boot($schedule);

        $this->registerSchedule($schedule);
        $this->registerModelObservers();
    }

    /**
     * Register module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(BackendUiNavigationServiceProvider::class);
        $this->app['translator']->addNamespace(
            'custom-module',
            base_path('modules/addons/CustomModule/lang')
        );

        $this->registerCommands();

        // Example only: put real module table changes in database/migrations.
        // For small demo/seed logic see Support\Installers\CustomModelTableInstaller.
        // app(\Modules\Addons\CustomModule\Support\Installers\CustomModelTableInstaller::class)->install();

        parent::register('CustomModule');
    }

    /**
     * Register Artisan commands provided by this module.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        $this->commands([
            CustomModuleCommand::class,
            IpmiBmcResetCommand::class,
        ]);
    }

    /**
     * Register scheduled commands.
     *
     * @return void
     */
    private function registerSchedule(Schedule $schedule): void
    {
        // Example: run the IPMI BMC-reset command every day at midnight.
        // Remove or adjust the schedule to match your needs.
        $schedule->command('custom-module:ipmi-bmc-reset')
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();

        // Example: run the generic sample command every hour.
        // $schedule->command('custom-module:command')->hourly();
    }

    /**
     * Register Eloquent observers.
     *
     * Observers are preferred over large inline model callbacks in the provider.
     *
     * @return void
     */
    private function registerModelObservers(): void
    {
        Device::observe(DeviceObserver::class);
    }
}
