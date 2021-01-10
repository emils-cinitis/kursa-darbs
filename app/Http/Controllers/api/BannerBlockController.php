<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;

class BannerBlockController extends Controller {

    /**
     * Get all banner bloks
     * 
     * @param Request $request Request which can contain banner UUID
     * 
     * @return Response JSON response with success or error
     */
    public function get(Request $request) {
        //Get passed ID
        $id = $request->input('id');
        $is_default_template = ($id == 0);

        //Create new banner block
        $response_array = [];

        //Get template
        try {
            if($is_default_template) {
                $template = Template::findOrFail(1);
            } else {
                $template = Template::findOrFail($id);
                $default_template = Template::findOrFail(1);
            }

        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Wrong template ID'
            ], 422);
        }


        try {
            //Select all blocks used by template
            $blocks = $template->blocks;

            //Set default blocks if template edit
            if(!$is_default_template) {
                $blocks = $blocks->merge($default_template->blocks);
            }

            $duplicates = [];
            $enabled_banner_types = ['giga' => false, 'tower' => false];

            if($is_default_template) {
                $enabled_banner_types = ['giga' => true, 'tower' => false];
            }

            //Cycle all blocks and reformat them
            foreach($blocks as $key => $block) {
                //Get block type
                $block_type = $block->block_type;

                //Get banner type
                $banner_type = strtolower($block->banner_type->title);
                
                //If duplicates hasn't got a key with banner type title, create one
                if(!array_key_exists($banner_type, $duplicates)) {
                    $duplicates[$banner_type] = [];
                }

                if(!array_key_exists($block_type->title, $duplicates[$banner_type])) {
                    //Add missing variable
                    $block_reformatted = $block->toArray();
                    if($is_default_template) {
                        $block_reformatted['enabled'] = true;
                    } else {
                        $block_reformatted['enabled'] = ($block_reformatted['template_id'] == $id);
                        if($block_reformatted['enabled']) {
                            $enabled_banner_types[$banner_type] = true;
                        }
                    }

                    //Remove unused variables
                    unset($block_reformatted['banner_type_id']);
                    unset($block_reformatted['block_type_id']);
                    unset($block_reformatted['template_id']);
                    unset($block_reformatted['banner_type']);
                    unset($block_reformatted['id']);

                    //If response hasn't got a key with banner type title, create one
                    if(!array_key_exists($banner_type, $response_array)) {
                        $response_array[$banner_type] = [];
                    }

                    //Add block to banner type
                    array_push($response_array[$banner_type], $block_reformatted);
                    $duplicates[$banner_type][$block_type->title] = true;
                }
            }

        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get template blocks'
            ], 500);
        }

        return response()->json([
            'status'    => 'success', 
            'blocks'    => $response_array,
            'banner_types' => $enabled_banner_types
        ], 200);
    }

}