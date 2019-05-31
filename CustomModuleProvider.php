<?php

namespace Modules\Addons\CustomModule;

use Components\Core\Support\Providers\ModuleServiceProvider;
use Components\Front\Core\Widgets\CASmartBoxGenerator;
use Components\Libs\Widgets\SmartBoxGenerator;
use Modules\Addons\CustomModule\Widgets\ClientArea\CustomWidgetServiceSummary;
use Modules\Addons\CustomModule\Widgets\CustomModuleDeviceWidget;
use Modules\Addons\CustomModule\Commands\CustomModuleCommand;

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
            $app['router']->group(['namespace' => 'Modules\Addons\CustomModule\Controller', 'prefix' => 'backend'], function() use($app)
            {
                $app['router']->get('/custom-module/tab1', ['as' => 'backend.custom.module.tab1', 'uses' => 'PageController@showTab1']);
                $app['router']->post('/custom-module/tab1/edit/quick', ['as' => 'backend.custom.module.tab1.edit.quick', 'uses' => 'PageController@quickEdit']);
                $app['router']->get('/custom-module/api', ['as' => 'backend.custom.module.tab2', 'uses' => 'PageController@apiRequest']);
                $app['router']->get('/custom-module/api/get-request', ['uses' => 'PageController@getRequest']);
                $app['router']->get('/custom-module/api/post-request', ['uses' => 'PageController@postRequest']);
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

        // $this->registerWidgets();
        // $this->registerCommand();
        // $this->clientAreaEvents();
        // $this->orderEvents();

        parent::register('CustomModule');
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

    private function registerCommand()
    {
        $this->app['events']->listen('artisan.command.register', function() {
            \Artisan::add(new CustomModuleCommand);
        });
    }
}