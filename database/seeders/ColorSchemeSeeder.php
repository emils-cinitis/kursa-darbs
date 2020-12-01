<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorSchemeSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        //ToDo: add actual color schemes
        $color_schemes = [
            [
                'title' => 'Black and white',
                'background_color' => '#000000FF',
                'text_color'    => '#FFFFFFFF',
                'cta_color' => '#000000FF',
                'user_uuid' => null
            ],
            [
                'title' => 'White and black',
                'background_color' => '#FFFFFFFF',
                'text_color'    => '#000000FF',
                'cta_color' => '#FFFFFFFF',
                'user_uuid' => null
            ]     
        ];

        DB::table('color_schemes')->insert($color_schemes);
    }
}
