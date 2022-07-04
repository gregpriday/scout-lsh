<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lsh_search_index', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('field')->default('');

            // We need to store 1024 bits
            for($i = 0; $i < 16; $i++) {
                $table->unsignedBigInteger("bit_{$i}");
            }

            $table->unique(['model_type', 'model_id', 'field']);
            $table->index(['model_type', 'model_id']);
        });
    }
};
