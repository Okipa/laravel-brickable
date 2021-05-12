<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHasOneConstrainedBrickableModelsTable extends Migration
{
    public function up(): void
    {
        Schema::create('has_one_constrained_brickable_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('has_one_constrained_brickable_models');
    }
}
