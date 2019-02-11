<?php

namespace Modules\Addons\CustomModule\Controller;

use Components\Front\Core\Widgets\CASmartBoxGenerator;
use Components\Front\Http\Controllers\ServicesController;
use Components\Front\Repositories\CAServicesRepository;
use Modules\Addons\CustomModule\Widgets\ClientArea\CustomInformationWidget;
use Modules\Provisioning\ServerProvisioningModule\Repositories\CAServersRepository;
use Modules\Provisioning\ServerProvisioningModule\Widgets\ClientArea\CABackToServiceWidget;

/**
 * Class ClientAreaController
 * @package Modules\Addons\CustomModule\Controller
 */
class ClientAreaController extends ServicesController
{
    /**
     * @var CAServersRepository
     */
    protected $servers;

    /**
     * SPMServicesController constructor.
     * @param CAServicesRepository $services
     * @param CAServersRepository $servers
     */
    public function __construct(CAServicesRepository $services, CAServersRepository $servers)
    {
        parent::__construct($services);

        $this->servers = $servers;
    }

    /**
     * @param $id
     * @return \View
     */
    public function showCustomPage($id)
    {
        $server = $this->servers->fetchServerForService($id);

        $service    = $this->services->fetchById($id);
        $module     = $service->module();

        $widgets = new CASmartBoxGenerator('clientarea.core.services.servers.custom.page.widgets', $service);
        $widgets->setNamespace([$service->id]);
        $widgets->addWidget(new CABackToServiceWidget($service));
        $widgets->addWidget(new CustomInformationWidget($service));

        return view('default::partials.services.summary', [
            'service' => $service,
            'module' => $module,
            'widgets' => $widgets,
            'server' => $server
        ]);
    }
}