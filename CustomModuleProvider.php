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
                $app['router']->get('/custom-module/tab2', ['as' => 'backend.custom.module.tab2', 'uses' => 'PageController@showTab2']);
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

            $app['router']->group(['namespace' => 'Modules\Addons\CustomModule\Controller\Api\v2', 'prefix' => 'api/v2'], function() use($app)
            {
                /**
                 * API Routes for Module
                 */
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