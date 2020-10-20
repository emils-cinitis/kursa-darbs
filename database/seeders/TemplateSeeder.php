<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $templates = [
            ['title' => 'Delfi']  
        ];

        DB::table('templates')->insert($templates);
    }
}
