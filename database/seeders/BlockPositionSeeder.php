<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BlockPositionSeeder extends Seeder {

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $block_positions = [
            //Giga
            [ 'width' => 300, 'height' => 250, 'left_offest' => 0  , 'top_offset' => 0  , 'z_index' => 1, 'template_id' => 1, 'banner_type_id' => 1, 'block_type_id' => 1 ],
            [ 'width' => 600, 'height' => 70 , 'left_offest' => 350, 'top_offset' => 50 , 'z_index' => 2, 'template_id' => 1, 'banner_type_id' => 1, 'block_type_id' => 2 ],
            [ 'width' => 600, 'height' => 60 , 'left_offest' => 350, 'top_offset' => 130, 'z_index' => 3, 'template_id' => 1, 'banner_type_id' => 1, 'block_type_id' => 3 ],
            [ 'width' => 100, 'height' => 40 , 'left_offest' => 800, 'top_offset' => 200, 'z_index' => 4, 'template_id' => 1, 'banner_type_id' => 1, 'block_type_id' => 4 ],
            //Tower
            [ 'width' => 300, 'height' => 250, 'left_offest' => 0  , 'top_offset' => 0  , 'z_index' => 1, 'template_id' => 1, 'banner_type_id' => 2, 'block_type_id' => 1 ],
            [ 'width' => 250, 'height' => 150, 'left_offest' => 25 , 'top_offset' => 280, 'z_index' => 2, 'template_id' => 1, 'banner_type_id' => 2, 'block_type_id' => 2 ],
            [ 'width' => 250, 'height' => 80 , 'left_offest' => 25 , 'top_offset' => 450, 'z_index' => 3, 'template_id' => 1, 'banner_type_id' => 2, 'block_type_id' => 3 ],
            [ 'width' => 80 , 'height' => 30 , 'left_offest' => 110, 'top_offset' => 550, 'z_index' => 4, 'template_id' => 1, 'banner_type_id' => 2, 'block_type_id' => 4 ],
        ];

        DB::table('block_positions')->insert($block_positions);
    }
}
