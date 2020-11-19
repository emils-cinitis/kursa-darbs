<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Template;

class BannerBlockController extends Controller {
    public function get(Request $request) {
        $id = $request->input('id');

        // Create view
        if($id == 0) {
            $response_array = [];

            try {
                $template = Template::findOrFail(1); //Select first template as default

            } catch(Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get default template'
                ], 500);
            }


            try {
                $blocks = $template->blocks;

                foreach($blocks as $key => $block) {
                    $block_type = $block->block_type;

                    $banner_type = strtolower($block->banner_type->title);
                    
                    $block_reformatted = $block->toArray();
                    $block_reformatted['enabled'] = true;

                    unset($block_reformatted['banner_type_id']);
                    unset($block_reformatted['block_type_id']);
                    unset($block_reformatted['template_id']);
                    unset($block_reformatted['banner_type']);
                    unset($block_reformatted['id']);

                    if(!array_key_exists($banner_type, $response_array)) {
                        $response_array[$banner_type] = [];
                    }

                    array_push($response_array[$banner_type], $block_reformatted);
                }

            } catch(Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get default template blocks'
                ], 500);
            }

            return response()->json([
                'status'    => 'success', 
                'blocks'    => $response_array
            ], 200);

        } else { // Update view
            //ToDo: update template block positions
        }
    }

}