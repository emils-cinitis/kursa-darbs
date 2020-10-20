<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() {
        $this->call([
            UserRoleSeeder::class,
            BannerTypeSeeder::class,
            BlockTypeSeeder::class,
            ColorSchemeSeeder::class,
            TemplateSeeder::class,
            BannerTemplateTypeSeeder::class,
            BlockPositionSeeder::class
        ]);
    }
}
