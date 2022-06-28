<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lsh-search-index', function (Blueprint $table) {
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('field')->default('');

            // Create 64 unsigned 8 bit integer fields
            for ($i = 0; $i < 96; $i++) {
                $table->unsignedTinyInteger("bit_{$i}");
            }

            $table->unique(['model_type', 'model_id', 'field']);
        });
    }
};
