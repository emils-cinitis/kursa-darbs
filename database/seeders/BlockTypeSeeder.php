<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlockTypeSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $block_types = [
            ['title' => 'Main text'], 
            ['title' => 'Sub text'], 
            ['title' => 'Image'], 
            ['title' => 'Call to action']
        ];

        DB::table('block_types')->insert($block_types);
    }
}
