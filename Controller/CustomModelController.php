<?php

namespace Modules\Addons\CustomModule\Controller;

use Components\Libs\Grid\DataTableResponse;
use Components\Libs\Grid\GridTableGenerator;
use Modules\Addons\CustomModule\Model\CustomModel;

/**
 * Class CustomModelController
 * @package Modules\Addons\CustomModule\Controller
 */
class CustomModelController extends OutputController
{
    /**
     * @return \View
     */
    public function index()
    {
        $models = CustomModel::filtered($this->routeFilters());

        // Create new table generator instance
        $table = new GridTableGenerator($models);
        $table->setColumns(config('CustomModule::customModel.columns'));

        $filters = config('CustomModule::customModel.filters');

        foreach($filters as $name => $params) {
            $table->addFilter($name, $params);
        }

        $table->setFiltersView('CustomModule::tab3.filters');

        // Check whether you need a JSON response
        if ($this->wantsCleanJsonResponse()) {
            $dataResponse = new DataTableResponse($models);
            $dataResponse->setColumns(config('CustomModule::customModel.columns'));

            return response_json($dataResponse->show());
        }

        return view('CustomModule::tab3.summary', ['table' => $table]);
    }
}