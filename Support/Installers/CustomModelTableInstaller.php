<?php

namespace Modules\Addons\CustomModule\Support\Installers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Addons\CustomModule\Model\CustomModel;

/**
 * Seeds demo rows for the CustomModel table.
 *
 * The table itself is created by the module migration in database/migrations.
 */
class CustomModelTableInstaller
{
    public function install(): void
    {
        if (! Schema::hasTable('custom_module_table') || CustomModel::query()->exists()) {
            return;
        }

        $items = DB::table('items')->pluck('id', 'id')->toArray();
        $types = DB::table('item_types')->pluck('name', 'name')->toArray();

        if (empty($items) || empty($types)) {
            return;
        }

        for ($i = 1; $i <= 15; $i++) {
            CustomModel::create([
                'name' => sprintf('Test Item %s', $i),
                'description' => 'Auto Generated Item',
                'item_id' => array_rand($items),
                'type' => array_rand($types),
            ]);
        }
    }
}
