<?php

namespace Modules\Addons\CustomModule;

use App\Models\Device;
use App\Providers\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Modules\Addons\CustomModule\BackendUi\CustomModuleBackendUiProvider;
use Modules\Addons\CustomModule\Commands\CustomModuleCommand;
use Modules\Addons\CustomModule\Commands\IpmiBmcResetCommand;
use Modules\Addons\CustomModule\Observers\DeviceObserver;
use Throwable;

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
        $this->registerBackendUi();
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
     * Register the 2.0 panel parts. EasyDCIM catches exceptions of a module, but not errors (a missing class, a wrong
     * argument), which would stop every page, so a failing registration only leaves the panel parts out.
     *
     * @return void
     */
    private function registerBackendUi(): void
    {
        try {
            $this->app->register(CustomModuleBackendUiProvider::class);
        } catch (Throwable $exception) {
            Log::error('[CustomModule] The 2.0 panel registration failed. Custom Module 2.x requires EasyDCIM 2.0.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
        }
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
        // Example: run the sample command, which only prints text, every day. Never schedule a command that touches
        // hardware (custom-module:ipmi-bmc-reset resets the BMC of every IPMI device) in a sample.
        $schedule->command('custom-module:command')
            ->daily()
            ->withoutOverlapping()
            ->onOneServer();
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
