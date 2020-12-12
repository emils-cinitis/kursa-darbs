<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerTypeSeeder extends Seeder {
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $banner_types = [
            [
                'title' => 'Giga',
                'width' => 970,
                'height' => 250
            ], 
            [
                'title' => 'Tower',
                'width' => 300,
                'height' => 600
            ]
        ];

        DB::table('banner_types')->insert($banner_types);
    }
}
