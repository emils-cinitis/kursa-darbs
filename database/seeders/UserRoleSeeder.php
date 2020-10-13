<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleSeeder extends Seeder {
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        DB::table('user_roles')->insert([
            'id' => 1,
            'role' => 'Blocked'
        ]);
        DB::table('user_roles')->insert([
            'id' => 2,
            'role' => 'Standard'
        ]);
        DB::table('user_roles')->insert([
            'id' => 3,
            'role' => 'Moderator'
        ]);
        DB::table('user_roles')->insert([
            'id' => 4,
            'role' => 'Admin'
        ]);
    }
}
