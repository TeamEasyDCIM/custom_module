<?php

namespace Modules\Addons\CustomModule\Controller;

use Components\Helpers\Backend\TabsGenerator;
use Components\Modules\Http\Controllers\ModuleController;

/**
 * Class OutputController
 * @package Modules\Addons\CustomModule\Controller
 */
class OutputController extends ModuleController
{
    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        // Sets the page title
        $this->setPageTitle(trans('CustomModule::backend.custom_module'));

        // Generate Dashboard Menu
        $tabs = new TabsGenerator('', null, configuration_get('CustomModule::dashboard.tabs.menu'), false);

        view_share([
            'tabs' => $tabs->render(),
        ]);
    }

    /**
     * @return string
     */
    public function getIndex()
    {
        return redirect_route('backend.custom.module.tab1');
    }
}