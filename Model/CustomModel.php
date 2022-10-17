<?php

namespace Modules\Addons\CustomModule\Model;

use \Components\Core\Essence\Essence;
use Item;

class CustomModel extends Essence
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    public $table = 'custom_module_table';

    /**
     * Determines which fields should be fillable
     * 
     * @var array
     */
    public $fillable = [
        'item_id',
        'name',
        'type',
        'description',
        'created_at',
        'updated_at'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * @param $query
     * @param array $filters
     * @param array $relations
     * @return mixed
     */
    public function scopeFiltered($query, $filters = [], array $relations = [])
    {
        $query->with($relations);

        if(array_get($filters, 'name')) {
            $query->where('name', array_get($filters, 'name'));
        }

        if(array_get($filters, 'type')) {
            $query->where('type', array_get($filters, 'type'));
        }

        if(array_get($filters, 'description')) {
            $query->where('description', array_get($filters, 'description'));
        }

        if(array_get($filters, 'item_id')) {
            $query->where('item_id', array_get($filters, 'item_id'));
        }

        return $query;
    }

    /**
     * @return string
     */
    public function getLabeledItemAttribute()
    {
        $label = trans('backend/global.unknown');

        $item = $this->getAttribute('item');

        if($item instanceof Item) {
            $label = $this->getAttribute('item')->getLabeledSummaryNameAttribute();
        }

        return $label;
    }
}
