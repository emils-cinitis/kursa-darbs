<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannerTemplateTypes extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bannner_template_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('templates');
            $table->foreignId('banner_type_id')->constrained('banner_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('bannner_template_types');
    }
}
