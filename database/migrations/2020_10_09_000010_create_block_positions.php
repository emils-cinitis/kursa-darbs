<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlockPositions extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('block_positions', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('width');
            $table->smallInteger('height');
            $table->smallInteger('left_offest');
            $table->smallInteger('top_offset');
            $table->smallInteger('z_index');
            $table->foreignId('template_id')->constrained('templates');
            $table->foreignId('banner_type_id')->constrained('banner_types');
            $table->foreignId('block_type_id')->constrained('block_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('block_positions');
    }
}
