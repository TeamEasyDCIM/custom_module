<?php

use Modules\Addons\CustomModule\Model\CustomModel;

return array
(
    'columns' => array
    (
        'id' => array('label' => 'backend/global.id', 'sortable' => true, 'editable' => false),
        'item_id' => array('label' => 'backend/global.item', 'sortable' => true, 'editable' => false, 'present' => 'labeledItem'),
        'name' => array('label' => 'backend/global.name', 'sortable' => true, 'editable' => false, 'modifier' => '<strong>:value</strong>', 'attributes' => [
            'data' => ['data-mobile-header' => 1]
        ]),
        'type' => array('label' => 'backend/global.type', 'sortable' => true, 'editable' => false, 'attributes' => []),
        'description' => array('label' => 'backend/global.description', 'sortable' => true, 'editable' => false, 'attributes' => []),
        'created_at' => array('label' => 'backend/global.date', 'sortable' => true, 'editable' => false, 'type' => 'Date'),
        'actions' => array
        (
            'label' => '',
            'type' => 'Actions',
            'sortable' => false,
            'availableActions' => array(),
            'attributes' => [
                'style' => ['min-width' => '140px']
            ],
            'customActions' => [
                'summary' => function($model) {
                    /*** @var CustomModel $model */
                    return [
                        'url' => route('backend.custom.module.tab3', [
                            'id' => $model->getAttribute('id'),
                        ]),
                        'name' => trans('backend/global.summary'),
                        'icon' => 'fa fa-folder',
                    ];
                },
            ],
        ),
    ),
    'filters' => array
    (
        'item_id' => [
            'label' => trans('backend/global.item'),
            'type' => 'autocomplete',
            'url' => route('backend.autocomplete.items.list'),
            'values' => 'autocomplete.backend.items.list',
            'values_labels' => \DB::table('items')->select(['id', 'label'])->lists('label', 'id')
        ],
        'name' => ['label' => trans('backend/global.label'), 'type' => 'text'],
        'type' => ['label' => trans('backend/global.type'), 'type' => 'text'],
        'description' => ['label' => trans('backend/global.description'), 'type' => 'text'],
    ),
);