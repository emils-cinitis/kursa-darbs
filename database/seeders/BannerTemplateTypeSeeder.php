<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerTemplateTypeSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $banner_template_types = [
            [
                'template_id' => 1,
                'banner_type_id' => 1
            ],
            [
                'template_id' => 1,
                'banner_type_id' => 2
            ]
        ];

        DB::table('banner_template_types')->insert($banner_template_types);
    }
}
