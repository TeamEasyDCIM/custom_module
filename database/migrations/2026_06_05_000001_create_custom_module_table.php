<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('custom_module_table')) {
            return;
        }

        Schema::create('custom_module_table', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('item_id');
            $table->string('name', 255)->default('');
            $table->string('type', 48);
            $table->text('description')->default('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_module_table');
    }
};
