<?php

declare(strict_types=1);

namespace Modules\Addons\CustomModule;

use App\Models\Device;
use App\Models\Order;
use App\Models\Service;
use ArrayObject;
use EasyDcim\Components\Provisioning\Log\LogService;
use EasyDcim\Components\Provisioning\Modules\ProvisioningModuleInterface;
use Illuminate\Support\Facades\Log;

/**
 * Backend domain event subscriber for Custom Module.
 *
 * EasyDCIM automatically subscribes `{ModuleName}EventHandler` for active addons.
 * Keep domain event automation here; the 2.0 panel parts (menu, pages, Servers list and
 * device Summary extensions) are registered in
 * {@see \Modules\Addons\CustomModule\BackendUi\CustomModuleBackendUiProvider}.
 */
class CustomModuleEventHandler
{
    /**
     * Register domain event listeners for OS installation, service lifecycle, and provisioning.
     *
     * @param  mixed  $events  Laravel event dispatcher instance.
     */
    public function subscribe(mixed $events): void
    {
        // OS installation lifecycle examples.
        $events->listen('easydcim.modules: os.remote.provisioning.start:during', [$this, 'onOsInstallationStart']);
        $events->listen('easydcim.modules: os.remote.provisioning.cancel:during', [$this, 'onOsInstallationCancel']);
        $events->listen('easydcim.modules: os.remote.provisioning.end:during', [$this, 'onOsInstallationEnd']);

        // Optional order/provisioning examples. Uncomment when you want to run them.
        // $events->listen('easydcim.service.after: activate', [$this, 'afterServiceActivate']);
        // $events->listen('easydcim.service.after: terminate', [$this, 'afterServiceTerminate']);
        // $events->listen('easydcim.provisioning: server.service.suspend', [$this, 'registerSuspendOrderAction']);
        // $events->listen('easydcim.provisioning: server.service.unsuspend', [$this, 'registerUnsuspendOrderAction']);
        // $events->listen('easydcim.provisioning: serverprovisioningmodule.suspend.after', [$this, 'afterProvisioningSuspend']);
        // $events->listen('easydcim.provisioning: serverprovisioningmodule.unsuspend.after', [$this, 'afterProvisioningUnsuspend']);
    }

    /**
     * Handle OS installation start event.
     *
     * @param  Device  $device  Target device undergoing OS installation.
     * @param  array<string, mixed>  $deviceConfiguration  Device provisioning configuration.
     * @param  array<string, mixed>  $serverConfiguration  Provisioning server configuration.
     */
    public function onOsInstallationStart(Device $device, array $deviceConfiguration, array $serverConfiguration): void
    {
        $this->updateOsStatus($device, '[Custom Module] Start installation');
    }

    /**
     * Handle OS installation cancellation event.
     *
     * @param  Device  $device  Target device whose OS installation was cancelled.
     * @param  array<string, mixed>  $deviceConfiguration  Device provisioning configuration.
     * @param  array<string, mixed>  $serverConfiguration  Provisioning server configuration.
     */
    public function onOsInstallationCancel(Device $device, array $deviceConfiguration, array $serverConfiguration): void
    {
        $this->updateOsStatus($device, '[Custom Module] Cancel installation');
    }

    /**
     * Handle OS installation completion event.
     *
     * @param  Device  $device  Target device that finished OS installation.
     * @param  array<string, mixed>  $deviceConfiguration  Device provisioning configuration.
     * @param  array<string, mixed>  $serverConfiguration  Provisioning server configuration.
     */
    public function onOsInstallationEnd(Device $device, array $deviceConfiguration, array $serverConfiguration): void
    {
        $this->updateOsStatus($device, '[Custom Module] Completion of the installation');
    }

    /**
     * React after a client service is activated.
     *
     * @param  mixed  $order  Activated order model.
     */
    public function afterServiceActivate(mixed $order): void
    {
        /** @var Service $service */
        $service = $order->getAttribute('service');
        $device = $service->relatedItem();

        Log::info('[CustomModule] Service activated', [
            'service_id' => $service->getKey(),
            'device_id' => optional($device)->getKey(),
        ]);

        if ($device) {
            $device->forceAssignMetadata([
                'Custom VNC Username' => 'vnc_user',
                'Custom VNC Password' => 'vnc_pass',
            ]);
        }
    }

    /**
     * React after a client service is terminated.
     *
     * @param  mixed  $order  Terminated order model.
     */
    public function afterServiceTerminate(mixed $order): void
    {
        /** @var Service $service */
        $service = $order->getAttribute('service');
        $device = $service->relatedItem();
        $client = $order->getAttribute('user');

        Log::info('[CustomModule] Service terminated', [
            'service_id' => $service->getKey(),
            'device_id' => optional($device)->getKey(),
            'client_id' => optional($client)->getKey(),
        ]);

        if ($device) {
            $device->forceAssignMetadata([
                'Custom VNC Username' => '',
                'Custom VNC Password' => '',
            ]);
        }
    }

    /**
     * Register a custom suspend order action option.
     *
     * @param  ArrayObject<string, string>  $options  Available suspend action options.
     * @return ArrayObject<string, string> Updated options.
     */
    public function registerSuspendOrderAction(ArrayObject $options): ArrayObject
    {
        $options['custom_module_action_suspend'] = '[Custom Module] Custom Suspend Action';

        return $options;
    }

    /**
     * Register a custom unsuspend order action option.
     *
     * @param  ArrayObject<string, string>  $options  Available unsuspend action options.
     * @return ArrayObject<string, string> Updated options.
     */
    public function registerUnsuspendOrderAction(ArrayObject $options): ArrayObject
    {
        $options['custom_module_action_unsuspend'] = '[Custom Module] Custom Unsuspend Action';

        return $options;
    }

    /**
     * Run custom logic after a provisioning module suspends a service.
     *
     * @param  Order  $order  Affected order.
     * @param  Device  $device  Provisioned device.
     * @param  LogService  $logger  Provisioning log service.
     * @param  ProvisioningModuleInterface  $module  Active provisioning module.
     * @param  array<int, string>  $configuration  Enabled suspend actions.
     */
    public function afterProvisioningSuspend(
        Order $order,
        Device $device,
        LogService $logger,
        ProvisioningModuleInterface $module,
        array $configuration,
    ): void {
        if (in_array('custom_module_action_suspend', $configuration, true)) {
            module_provisioning_log($logger->getLogger(), $module, '[Custom Module] Trying to run custom suspend action');
        }
    }

    /**
     * Run custom logic after a provisioning module unsuspends a service.
     *
     * @param  Order  $order  Affected order.
     * @param  Device  $device  Provisioned device.
     * @param  LogService  $logger  Provisioning log service.
     * @param  ProvisioningModuleInterface  $module  Active provisioning module.
     * @param  array<int, string>  $configuration  Enabled unsuspend actions.
     */
    public function afterProvisioningUnsuspend(
        Order $order,
        Device $device,
        LogService $logger,
        ProvisioningModuleInterface $module,
        array $configuration,
    ): void {
        if (in_array('custom_module_action_unsuspend', $configuration, true)) {
            module_provisioning_log($logger->getLogger(), $module, '[Custom Module] Trying to run custom unsuspend action');
        }
    }

    /**
     * Update the OS installation status message for the device's MAC address.
     *
     * @param  Device  $device  Target device.
     * @param  string  $message  Status message to record.
     */
    private function updateOsStatus(Device $device, string $message): void
    {
        $mac = $device->getMetaAttribute('MAC Address');

        if ($mac) {
            os_update_status_for_mac($mac, $message);
        }
    }
}
