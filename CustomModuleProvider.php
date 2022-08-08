<?php

namespace Modules\Addons\CustomModule;

use Components\Core\Support\Providers\ModuleServiceProvider;
use Components\Front\Core\Widgets\CASmartBoxGenerator;
use Components\Helpers\Backend\TabsGenerator;
use Components\Libs\Widgets\SmartBoxGenerator;
use EasyDcim\Components\Provisioning\Log\LogService;
use EasyDcim\Components\Provisioning\Modules\ProvisioningModuleInterface;
use Illuminate\Support\Collection;
use Modules\Addons\CustomModule\Widgets\ClientArea\CustomWidgetServiceSummary;
use Modules\Addons\CustomModule\Widgets\CustomModuleDeviceWidget;
use Modules\Addons\CustomModule\Commands\CustomModuleCommand;
use Modules\Addons\CustomModule\Commands\IpmiBmcResetCommand;
use Modules\Addons\CustomModule\Widgets\CustomModuleDashboardWidget;

/**
 * Class CustomModuleProvider
 * @package Modules\Addons\CustomModule
 */
class CustomModuleProvider extends ModuleServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        parent::boot('CustomModule');
    }

    /**
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $app['events']->listen('common.router.after', function() use($app)
        {
            /**
             * Backend Routes
             */
            $app['router']->group(['namespace' => 'Modules\Addons\CustomModule\Controller', 'prefix' => 'backend', 'before' => 'acl', 'perm' => 'core.modules.addons.custom-module'], function() use($app)
            {
                $app['router']->get('/custom-module/tab1', ['as' => 'backend.custom.module.tab1', 'uses' => 'PageController@showTab1']);
                $app['router']->get('/custom-module/tab1/{id}/action/enable', ['as' => 'backend.custom.module.tab1.action.enable', 'uses' => 'PageController@enableAction']);
                $app['router']->get('/custom-module/tab1/{id}/action/disable', ['as' => 'backend.custom.module.tab1.action.disable', 'uses' => 'PageController@disableAction']);
                $app['router']->post('/custom-module/tab1/edit/quick', ['as' => 'backend.custom.module.tab1.edit.quick', 'uses' => 'PageController@quickEdit']);
                $app['router']->get('/custom-module/api', ['as' => 'backend.custom.module.tab2', 'uses' => 'PageController@apiRequest']);
                $app['router']->get('/custom-module/api/get-request', ['uses' => 'PageController@getRequest']);
                $app['router']->get('/custom-module/api/post-request', ['uses' => 'PageController@postRequest']);
                $app['router']->get('/custom-module/devices/{id}/custom-tab', [
                    'as' => 'backend.custom.module.device.tab', 
                    'uses' => 'DeviceController@showCustomTab'
                ]);
            });

            /**
             * Test API Route
             */
            $app['router']->group(['namespace' => 'Modules\Addons\CustomModule\Controller\Api\v2', 'prefix' => 'backend'], function() use($app)
            {
                /**
                 * Devices
                 */
                $app['router']->get('/custom-module/api/device/create', ['uses' => 'DevicesController@createDevice']);
                $app['router']->get('/custom-module/api/device/boot', ['uses' => 'DevicesController@bootDevice']);
                $app['router']->get('/custom-module/api/device/reboot', ['uses' => 'DevicesController@rebootDevice']);
                $app['router']->get('/custom-module/api/device/shutdown', ['uses' => 'DevicesController@shutdownDevice']);
                $app['router']->get('/custom-module/api/device/list', ['uses' => 'DevicesController@listDevices']);
                $app['router']->get('/custom-module/api/device/show', ['uses' => 'DevicesController@showDevice']);

                /**
                 * Orders
                 */
                $app['router']->get('/custom-module/api/order/create', ['uses' => 'OrdersController@createOrder']);
                $app['router']->get('/custom-module/api/order/update', ['uses' => 'OrdersController@updateOrder']);
                $app['router']->get('/custom-module/api/order/delete', ['uses' => 'OrdersController@deleteOrder']);

                /**
                 * Locations
                 */
                $app['router']->get('/custom-module/api/location/create-location', ['uses' => 'LocationsController@createLocation']);
                $app['router']->get('/custom-module/api/location/create-floor', ['uses' => 'LocationsController@createFloor']);
                $app['router']->get('/custom-module/api/location/create-rack', ['uses' => 'LocationsController@createRack']);

                /**
                 * Item Types
                 */
                $app['router']->get('/custom-module/api/item-type/create', ['uses' => 'ItemTypesController@createType']);
                $app['router']->get('/custom-module/api/item-type/update', ['uses' => 'ItemTypesController@updateType']);

                /**
                 * Device Bays
                 */
                $app['router']->get('/custom-module/api/device-bay/list', ['uses' => 'DeviceBaysController@listBays']);
                $app['router']->get('/custom-module/api/device-bay/create', ['uses' => 'DeviceBaysController@createBay']);
                $app['router']->get('/custom-module/api/device-bay/update', ['uses' => 'DeviceBaysController@updateBay']);
                $app['router']->get('/custom-module/api/device-bay/delete', ['uses' => 'DeviceBaysController@deleteBay']);

                /**
                 * Item Ports
                 */
                $app['router']->get('/custom-module/api/item-port/create', ['uses' => 'ItemPortsController@createPort']);

                /**
                 * IP Address Management Module
                 */
                $app['router']->get('/custom-module/api/ipam/vlan/list', ['uses' => 'IpamController@listVlans']);
                $app['router']->get('/custom-module/api/ipam/vlan/show', ['uses' => 'IpamController@showVlan']);
                $app['router']->get('/custom-module/api/ipam/vlan/create', ['uses' => 'IpamController@createVlan']);
                $app['router']->get('/custom-module/api/ipam/vlan/update', ['uses' => 'IpamController@updateVlan']);
                $app['router']->get('/custom-module/api/ipam/vlan/delete', ['uses' => 'IpamController@deleteVlan']);
                $app['router']->get('/custom-module/api/ipam/ip/assign', ['uses' => 'IpamController@assignIpAddress']);

                /**
                 * Items
                 */
                $app['router']->get('/custom-module/api/item/list', ['uses' => 'ItemsController@listItems']);
                $app['router']->get('/custom-module/api/item/show', ['uses' => 'ItemsController@showItem']);

                /**
                 * Graphs
                 */
                $app['router']->get('/custom-module/api/graphs/render', ['uses' => 'GraphsController@renderGraph']);
            });

            /**
             * IPAM Routes
             */
            $app['router']->group(['namespace' => 'Modules\Addons\CustomModule\Controller\Ipam', 'prefix' => 'backend'], function() use($app)
            {
                $app['router']->get('custom-module/ipam/list-subnets', [
                    'uses' => 'SubnetsController@listSubnets'
                ]);
                $app['router']->get('custom-module/ipam/update-empty-gateways', [
                    'uses' => 'SubnetsController@updateEmptyGateways'
                ]);
            });

            /**
             * Client Area Routes
             */
            $app['router']->group(['namespace' => 'Modules\Addons\CustomModule\Controller'], function() use($app)
            {
                $app['router']->get('services/{id}/custom/page', [
                    'as' => 'clientarea.core.services.custom.page',
                    'uses' => 'ClientAreaController@showCustomPage'
                ])->where(array('id' => '^\d+$'));
            });
        });

        $app['translator']->addNamespace('custom-module', base_path().'/modules/addons/CustomModule/lang');

        /**
         * Adds new features within the device
         */
        $this->deviceEvents();

        /**
         * Registers new commands executed in the background
         */
        // $this->registerCommand();

        /**
         * Registers a new page in the client area section of the service summary view
         */
        // $this->clientAreaEvents();

        /**
         * Records new function performed during order action
         */
        // $this->orderEvents();
        // $this->registerOrderSettings();
        // $this->serviceOrderActions();

        /**
         * Adds custom columns to the table
         */
        // $this->tableColumns();

        /**
         * Adds custom scripts and CSS styles
         */
        // $this->customTemplateScripts();

        /**
         * Blocks access to the backend section
         */
        // $this->restrictBackendAccess();

        /**
         * Adds new widget to Dashboard section
         */
        $this->dashboardWidgets();

        parent::register('CustomModule');
    }

    /**
     * Adds new features for the device
     *
     * @return void
     */
    private function deviceEvents()
    {
        /**
         * Adds widget to device summary view
         */
        $this->registerWidgets();
        
        /**
         * Adds a new tab in the server view
        */
        $this->registerTabs();
    }

    /**
     * Adds new widget to Dashboard section
     *
     * @return void
     */
    private function dashboardWidgets()
    {
        $this->app['events']->listen('backend.dashboard', function(SmartBoxGenerator $widgets) {
            $widgets->registerBox( new CustomModuleDashboardWidget());
        });
    }

    /**
     * If the incoming address matches against any value in the array,
     * the function will deny access with a redirect header to the EasyDCIM base URL
     */
    private function restrictBackendAccess()
    {
        if($this->app['request']->is('backend*')) {
            $allowFrom = [
               '192.168.56.100',
               '192.168.56.200'
            ];

            if(! in_array($_SERVER['REMOTE_ADDR'], $allowFrom)) {
                header('Location: '.app_url());
                exit();
            }
        }
    }

    /**
     * ClientArea Events like new sidebar, widgets etc.
     */
    private function clientAreaEvents()
    {
        $this->app['events']->listen('clientarea.core.sidebars', function(\ArrayObject $sidebars) {
            $params = $this->app['ca.params'];

            if($params->service instanceof \Service && $params->service->getAttribute('type') == 'Server') {
                $menu = $this->app['menu.manager']->createMenu('Custom Sidebar');

                $menu->addLink('External Link', [
                    'to' => 'https://www.easydcim.com'
                ], ['before' => '<i class="fa fa-plus"></i>', 'id' => 1]);
                $menu->addLink('Internal Link', [
                    'route' => ['clientarea.core.services.custom.page', 'id' => $params->service->id]
                ], ['before' => '<i class="fa fa-minus"></i>', 'id' => 1]);

                $sidebars['custom_sidebar'] = $menu;
            }
        });

        $this->app['events']->listen('clientarea.core.services.summary.widgets', function(CASmartBoxGenerator $generator, \Service $service) {
            if($service instanceof \Service) {
                if($service->getAttribute('type') == 'Server') {
                    $generator->addWidget(new CustomWidgetServiceSummary($service));
                }
            }
        });
    }

    /**
     * @return void
     */
    private function registerWidgets()
    {
        $this->app['events']->listen('backend.devices.summary', function(SmartBoxGenerator $widgets) {
            $widgets->registerBox( new CustomModuleDeviceWidget());
        });
    }

    /**
     * @return void
     */
    private function registerTabs()
    {
        $this->app['events']->listen('easydcim.server.tabs: render', function(TabsGenerator $tabGenerator) {
            $tabGenerator->appendTab(
                [
                    'url' => route('backend.custom.module.device.tab', array('id' => $tabGenerator->model->id)),
                    'title' => 'Custom Device Tab'
                ]
            );
        });
    }

    /**
     * Events triggered during action on services
     */
    private function orderEvents()
    {
        /**
         * After activate service
         */
        \Event::listen('easydcim.service.after: activate', function(\Order $order) {
            /**
             * @var $service \Service
             */
            $service = $order->getAttribute('service');

            /**
             * @var $device \Device
             */
            $device = $service->relatedItem();

            file_put_contents(base_path('/tmp/device_dump.log'), $device->toJson());

            /**
             * Assign metadata to device
             */
            $device->forceAssignMetadata([
                'Custom VNC Username' => 'test_username new',
                'Custom VNC Password' => 'test_password new'
            ]);
        });

        /**
         * After terminate service
         */
        \Event::listen('easydcim.service.after: terminate', function(\Order $order) {
            /**
             * @var $service \Service
             */
            $service = $order->getAttribute('service');

            echo '<h1>Service</h1>';
            d($service->toArray());

            /**
             * @var $client \User
             */
            $client = $order->getAttribute('user');

            echo '<h1>Client</h1>';
            d($client->toArray());

            /**
             * @var $device \Device
             */
            $device = $service->relatedItem();

            /**
             * Assign metadata to device
             */
            $device->forceAssignMetadata([
                'Custom VNC Username' => '',
                'Custom VNC Password' => ''
            ]);

            echo '<h1>Device</h1>';
            d($device->toArray());

            echo '<h1>Device Metadata</h1>';
            d($device->getMetaListAttribute());
        });
    }

    /**
     * Register a new command
     */
    private function registerCommand()
    {
        $this->app['events']->listen('artisan.command.register', function() {
            \Artisan::add(new CustomModuleCommand);
            \Artisan::add(new IpmiBmcResetCommand);
        });
    }

    /**
     * Add additional columns to the table
     */
    private function tableColumns()
    {
        $this->app['events']->listen('easydcim.grid: columns', function(Collection $columns, $modelName, $path) {
            if($modelName == 'server') {
                $columns->put('a_column', [
                    'label' => trans('custom-module::backend.column_1'),
                    'sortable' => true,
                    'type' => 'Key',
                    'value' => function($model) {
                        return $model->id;
                    }
                ]);
            }

            return $columns;
        });
    }

    /**
     * Add additional JS and CSS
     */
    private function customTemplateScripts()
    {
        $this->app['events']->listen('easydcim.assets: template.js', function(\ArrayObject $scripts) {
            $scripts[] = base_path('modules/addons/CustomModule/templates/admin/default/assets/js/custom-app.js');

            return $scripts;
        });

        $this->app['events']->listen('easydcim.assets: template.css', function(\ArrayObject $scripts) {
            $scripts[] = base_path('modules/addons/CustomModule/templates/admin/default/assets/css/style.css');

            return $scripts;
        });
    }

    /**
     * Register orders additional functions
     *
     * @return void
     */
    private function registerOrderSettings()
    {
        $this->app['events']->listen('easydcim.provisioning: server.service.suspend', function(\ArrayObject $options) {
            $options['custom_module_action_suspend'] = '[Custom Module] Custom Suspend Action';

            return $options;
        });

        $this->app['events']->listen('easydcim.provisioning: server.service.unsuspend', function(\ArrayObject $options) {
            $options['custom_module_action_unsuspend'] = '[Custom Module] Custom Unsuspend Action';

            return $options;
        });
    }

    /**
     * @return void
     */
    private function serviceOrderActions()
    {
        $this->app['events']->listen('easydcim.provisioning: serverprovisioningmodule.suspend.after', function(
            \Order $order,
            \Device $device,
            LogService $logger,
            ProvisioningModuleInterface $module,
            $configuration
        ) {
            if(in_array('custom_module_action_suspend', $configuration)) {
                module_provisioning_log($logger->getLogger(), $module, '[Custom Module] Trying to run custom suspend action');
            }
        });

        $this->app['events']->listen('easydcim.provisioning: serverprovisioningmodule.unsuspend.after', function(
            \Order $order,
            \Device $device,
            LogService $logger,
            ProvisioningModuleInterface $module,
            $configuration
        ) {
            if(in_array('custom_module_action_unsuspend', $configuration)) {
                module_provisioning_log($logger->getLogger(), $module, '[Custom Module] Trying to run custom unsuspend action');
            }
        });
    }
}