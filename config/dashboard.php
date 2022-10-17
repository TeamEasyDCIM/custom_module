<?php

return array
(
    'tabs' => array
    (
        'menu' => array
        (
            array('url' => route('backend.custom.module.tab1'), 'icon' => 'fa fa-home', 'title' => trans('CustomModule::backend.dashboard')),
            array('url' => route('backend.custom.module.tab2'), 'icon' => 'fa fa-tasks', 'title' => trans('CustomModule::backend.api_test')),
            array('url' => route('backend.custom.module.tab3'), 'icon' => 'fa fa-cogs', 'title' => trans('CustomModule::backend.custom_model_table')),
        )
    ),
);