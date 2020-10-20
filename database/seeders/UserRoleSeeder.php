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
        $user_roles = [
            ['role' => 'Blocked'],
            ['role' => 'Standard'],
            ['role' => 'Moderator'],
            ['role' =>  'Admin']
        ];
        
        DB::table('user_roles')->insert($user_roles);
    }
}
