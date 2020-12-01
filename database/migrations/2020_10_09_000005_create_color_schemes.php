<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateColorSchemes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('color_schemes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('background_color');
            $table->string('text_color');
            $table->string('cta_color');
            $table->foreignUuid('user_uuid')->nullable()->references('uuid')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('color_schemes');
    }
}
