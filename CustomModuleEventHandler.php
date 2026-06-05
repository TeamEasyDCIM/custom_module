<?php

namespace Modules\Addons\CustomModule;

use App\Models\Device;
use App\Models\Order;
use App\Models\Service;
use Components\Helpers\Backend\MenuBuilder;
use Components\Helpers\Backend\TabsGenerator;
use Components\Libs\Widgets\SmartBoxGenerator;
use EasyDcim\Components\Provisioning\Log\LogService;
use EasyDcim\Components\Provisioning\Modules\ProvisioningModuleInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Addons\CustomModule\Widgets\CustomModuleDashboardWidget;
use Modules\Addons\CustomModule\Widgets\CustomModuleDeviceWidget;

/**
 * CustomModule event subscriber.
 *
 * EasyDCIM automatically subscribes {ModuleName}EventHandler for addons.
 * Put event wiring here instead of growing CustomModuleProvider.
 */
class CustomModuleEventHandler
{
    /**
     * Register module event listeners.
     *
     * @param mixed $events
     * @return void
     */
    public function subscribe($events): void
    {
        // Device page examples.
        $events->listen('backend.devices.summary', [$this, 'registerDeviceSummaryWidget']);
        $events->listen('easydcim.server.tabs: render', [$this, 'registerDeviceTab']);

        // Backend UI examples.
        $events->listen('backend.dashboard', [$this, 'registerDashboardWidget']);
        $events->listen('easydcim.grid: columns', [$this, 'registerGridColumns']);
        $events->listen('easydcim:backend.menu.render:after', [$this, 'registerBackendMenuItem']);

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

        // Optional asset example. Uncomment when you add real files in templates/admin/default/assets.
        // $events->listen('easydcim.assets: template.js', [$this, 'registerTemplateScripts']);
        //$events->listen('easydcim.assets: template.css', [$this, 'registerTemplateStyles']);
    }

    public function registerDeviceSummaryWidget(SmartBoxGenerator $widgets): void
    {
        $widgets->registerBox(new CustomModuleDeviceWidget());
    }

    public function registerDeviceTab(TabsGenerator $tabGenerator): void
    {
        $tabGenerator->appendTab([
            'url' => route('backend.custom.module.device.tab', ['id' => $tabGenerator->model->id]),
            'title' => 'Custom Device Tab',
        ]);
    }

    public function registerDashboardWidget(SmartBoxGenerator $widgets): void
    {
        $widgets->registerBox(new CustomModuleDashboardWidget());
    }

    public function registerGridColumns(Collection $columns, string $modelName, string $path): Collection
    {
        if ($modelName !== 'server') {
            return $columns;
        }

        $columnKey = 'custom_module_value';
        $columnDefinition = [
            'label' => trans('custom-module::backend.custom_value'),
            'sortable' => false,
            'type' => 'Key',
            // Example: render any custom value computed by your module.
            // This can come from metadata, an external API, or another module table.
            'value' => static fn ($model) => sprintf('CustomModule #%s', $model->getKey()),
        ];

        // Insert the custom column directly before the Actions column
        // (in the server grid this column key is ':actions').
        $actionsColumnKey = $columns->has(':actions') ? ':actions' : 'actions';

        if (! $columns->has($actionsColumnKey)) {
            $columns->put($columnKey, $columnDefinition);

            return $columns;
        }

        $columns->forget($columnKey);
        $orderedColumns = $columns->all();
        $columns->forget($columns->keys()->all());

        foreach ($orderedColumns as $key => $definition) {
            if ($key === $actionsColumnKey) {
                $columns->put($columnKey, $columnDefinition);
            }

            $columns->put($key, $definition);
        }

        return $columns;
    }

    public function registerBackendMenuItem(MenuBuilder $menuBuilder, \ArrayObject $items): \ArrayObject
    {
        $items['devices']['children'][] = [
            'key' => 'devices.custom_page',
            'name' => 'Custom Page',
            'url' => route('backend.custom.module.tab1'),
        ];

        return $items;
    }

    public function onOsInstallationStart(Device $device, array $deviceConfiguration, array $serverConfiguration): void
    {
        $this->updateOsStatus($device, '[Custom Module] Start installation');
    }

    public function onOsInstallationCancel(Device $device, array $deviceConfiguration, array $serverConfiguration): void
    {
        $this->updateOsStatus($device, '[Custom Module] Cancel installation');
    }

    public function onOsInstallationEnd(Device $device, array $deviceConfiguration, array $serverConfiguration): void
    {
        $this->updateOsStatus($device, '[Custom Module] Completion of the installation');
    }

    public function afterServiceActivate($order): void
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

    public function afterServiceTerminate($order): void
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

    public function registerSuspendOrderAction(\ArrayObject $options): \ArrayObject
    {
        $options['custom_module_action_suspend'] = '[Custom Module] Custom Suspend Action';

        return $options;
    }

    public function registerUnsuspendOrderAction(\ArrayObject $options): \ArrayObject
    {
        $options['custom_module_action_unsuspend'] = '[Custom Module] Custom Unsuspend Action';

        return $options;
    }

    public function afterProvisioningSuspend(
        Order $order,
        Device $device,
        LogService $logger,
        ProvisioningModuleInterface $module,
        array $configuration
    ): void {
        if (in_array('custom_module_action_suspend', $configuration, true)) {
            module_provisioning_log($logger->getLogger(), $module, '[Custom Module] Trying to run custom suspend action');
        }
    }

    public function afterProvisioningUnsuspend(
        Order $order,
        Device $device,
        LogService $logger,
        ProvisioningModuleInterface $module,
        array $configuration
    ): void {
        if (in_array('custom_module_action_unsuspend', $configuration, true)) {
            module_provisioning_log($logger->getLogger(), $module, '[Custom Module] Trying to run custom unsuspend action');
        }
    }

    public function registerTemplateScripts(\ArrayObject $scripts): \ArrayObject
    {
        $scripts[] = base_path('modules/addons/CustomModule/templates/admin/default/assets/js/custom-app.js');

        return $scripts;
    }

    public function registerTemplateStyles(\ArrayObject $styles): \ArrayObject
    {
        $styles[] = base_path('modules/addons/CustomModule/templates/admin/default/assets/css/style.css');

        return $styles;
    }

    private function updateOsStatus(Device $device, string $message): void
    {
        $mac = $device->getMetaAttribute('MAC Address');

        if ($mac) {
            os_update_status_for_mac($mac, $message);
        }
    }
}
