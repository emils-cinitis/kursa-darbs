<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanners extends Migration {
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('banners', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->string('name');
            $table->string('main_text');
            $table->string('sub_text');
            $table->foreignId('color_scheme_id')->constrained('color_schemes');
            $table->foreignUuid('created_by')->references('uuid')->on('users');
            $table->foreignId('template_id')->constrained('templates');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('banners');
    }
}
