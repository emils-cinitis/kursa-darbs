<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model {
    use HasFactory;

    protected $fillable = [
        'title',
        'user_uuid'
    ];

    public function blocks() {
        return $this->hasMany('App\Models\BlockPosition', 'template_id');
    }

    public function banners() {
        return $this->hasMany('App\Models\Banner');
    }

    public function usedBlocks() {
        $blocks = $this->blocks;

        $blocks_used = [];
        foreach($blocks as $key => $block) {
            $block_type = $block->block_type_id;
            if(!array_key_exists($block_type, $blocks_used)) {
                $block_name = BlockType::findOrFail($block_type)->title;
                $block_name = strtolower($block_name);
                $block_name = str_replace(" ", "_", $block_name);
                $blocks_used[$block_type] = $block_name;
            }
        }

        $blocks_used = array_flip($blocks_used);
        foreach($blocks_used as $key => $block) {
            $blocks_used[$key] = true;
        }

        return $blocks_used;
    }

    public function bannerTypes() {
        $blocks = $this->blocks;

        $banner_types = [];
        foreach($blocks as $block) {
            $type = $block->banner_type->toArray();
            if(!in_array($type['title'], $banner_types)) array_push($banner_types, $type['title']);
        }

        return $banner_types;
    }

    public function bannerBlocksByTypes() {
        $blocks = $this->blocks;

        $banner_types = [];
        foreach($blocks as $block) {
            $type = $block->banner_type->toArray();
            $type_title = strtolower($type['title']);

            //Add banner type info array
            if(!array_key_exists($type_title, $banner_types)) {
                unset($type['id']);
                unset($type['title']);
                $banner_types[$type_title] = ['sizes' => $type, 'blocks' => [] ];
            }

            //Add block type to return info
            $block->block_type;
            $block_array = $block->toArray();

            //Remove unused info
            unset($block_array['id']);
            unset($block_array['banner_type']);
            unset($block_array['banner_type_id']);
            unset($block_array['block_type_id']);
            unset($block_array['template_id']);

            array_push($banner_types[$type_title]['blocks'], $block_array);
        }
        return $banner_types;
    }
}