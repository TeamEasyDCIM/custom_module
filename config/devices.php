<?php

return array
(
    'columns' => array
    (
        'device' => array
        (
            'id' => array('label' => 'backend/global.id', 'sortable' => true, 'editable' => false),
            'type_id' => array('label' => 'backend/global.type', 'sortable' => true, 'editable' => false, 'present' => 'labeledType'),
            'device_status' => array('label' => '', 'sortable' => true, 'present' => 'labeledDeviceStatus', 'editable' => false),
            'osImage' => array('label' => '', 'sortable' => false, 'type' => 'Image', 'width' => 30, 'lazyLoad' => 1, 'attributes' => [
                'style' => ['min-width' => '48px']
            ]),
            'label' => array('label' => 'backend/global.label', 'sortable' => true, 'url' => 'model:summaryURL', 'modifier' => '<strong>:value</strong>', 'attributes' => [
                'data' => ['data-mobile-header' => 1]
            ]),
            'model' => array('label' => 'backend/global.model', 'sortable' => true, 'modifier' => '<strong>:value</strong>', 'url' => 'model:summaryURL'),
            ':hostname' => array(
                'label' => 'backend/global.hostname',
                'editable' => false,
                'sortable' => true,
                'present' => 'Hostname',
                'orderByExpression' => function ($query, $orderDirection) {
                    $query->orderByRaw(vsprintf("(select value from %s as item_fields_values where item_fields_values.item_id = %s.id and field_id = %s) %s", [
                        table_name('item_fields_values'), table_name('items'), DB::table('types_fields')->where('slug', 'hostname')->pluck('id'), $orderDirection
                    ]));
                },
                'attributes' => [
                    'style' => ['min-width' => '120px']
                ]
            ),
            ':ipaddress' => array(
                'label' => 'backend/global.ip_address',
                'editable' => false,
                'sortable' => true,
                'present' => 'IPAddress',
                'orderByExpression' => function ($query, $orderDirection) {
                    $query->orderByRaw(vsprintf("(select value from %s as item_fields_values where item_fields_values.item_id = %s.id and field_id = %s) %s", [
                        table_name('item_fields_values'), table_name('items'), DB::table('types_fields')->where('slug', 'ip')->pluck('id'), $orderDirection
                    ]));
                },
                'attributes' => [
                    'style' => ['min-width' => '120px']
                ]
            ),
            'location_id' => array('label' => 'backend/global.location', 'editable' => true, 'sortable' => true),
            'user_id' => array('label' => 'backend/global.concated_name', 'editable' => true, 'sortable' => true),
            'uptime' => array('label' => 'backend/global.uptime', 'sortable' => false, 'type' => 'Key', 'modifier' => function ($model) {
                return $model->uptime;
            }),
            'custom_column' => array('label' => 'Custom Column', 'editable' => false, 'sortable' => false, 'value' => function($model) {
                return view('CustomModule::tab1.custom_column', ['model' => $model]);
            }),
            ':actions' => array
            (
                'label' => '',
                'type' => 'Actions',
                'sortable' => false,
                'availableActions' => [],
                'customActions' => [
                    'summary' => function($model) {
                        return [
                            'url' => route('backend.custom.module.tab1', [
                                'id' => $model->getAttribute('id'),
                            ]),
                            'name' => trans('backend/global.summary'),
                            'icon' => 'fa fa-folder',
                        ];
                    },
                ],
            ),
        )
    ),
);