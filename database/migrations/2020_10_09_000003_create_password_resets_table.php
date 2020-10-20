<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->timestamp('created_at');
            $table->foreignUuid('user_uuid')->references('uuid')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('password_resets');
    }
}
