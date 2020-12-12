<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Template;
use App\Models\BannerType;
use App\Models\BlockType;
use App\Models\BlockPosition;

class TemplateController extends Controller {

    private function getDefaultTemplates() {
        try {
            $templates = Template::where('user_uuid', NULL)->get();
            return $templates;
        } catch (Exception $e) {
            return null;
        }
    }

    private function getAllInfoFromTemplate(Template $template) {
        $response_array = array();

        $template_blocks = $template->blocks;
        foreach($template_blocks as $key => $block) {
            try {
                $banner_type = $block->banner_type;
                $block_type = $block->block_type;
                
            } catch(Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Cannot retreive template information'
                ], 500);
            }

            $block_array = $block->toArray();
            unset($block_array['template_id']);
            $block_array['block_type'] = $block_type->title;
            $block_array['banner_type'] = $banner_type->title;
            
            if(!array_key_exists($banner_type->id, $response_array)) $response_array[$banner_type->id] = array();

            array_push($response_array[$banner_type->id], $block_array);
        }

        return $response_array;
    }

    public function getAll(Request $request) {
        try {
            $user = User::find(Auth::user()->uuid);
        } catch (Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ], 422);
        }

        try {
            $templates = $user->templates;
            
            if($request->input('all') === true) {
                $default_templates = $this->getDefaultTemplates();
                if($default_templates !== null) {
                    $templates = array_merge(
                        $templates->toArray(), 
                        $default_templates->toArray()
                    );
                }
            }

            return response()->json([
                'status'    => 'success',
                'templates' => $templates
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user templates'
            ], 422);
        }
    }

    public function get(Request $request) {

        if(empty($request->input('id'))) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Missing template info'
            ], 422);
        }

        try {
            $template = Template::findOrFail($request->input('id'));

            if($template->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Template is not created by user'
                ], 422);
            }

            $template_info = $this->getAllInfoFromTemplate($template);

            return response()->json([
                'status'    => 'success',
                'template'  => $template_info
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive template'
            ], 422);
        }
    }

    public function store(Request $request) {
        try {
            $user_uuid = Auth::user()->uuid;
        } catch (Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'message'   => $validator->messages()
            ], 422);
        }

        if(empty($request->input('id'))) {
            //Create template
            try {
                $template_varaibles = [
                    'title' => $request->input('title'), 
                    'user_uuid' => $user_uuid 
                ];
                $template = Template::create($template_varaibles);

            } catch(Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Cannot save template'
                ], 500);
            }

            //Create block positions
            $banner_types = $request->input('banner_types');
            foreach($banner_types as $key => $banner_type) {
                if($banner_type['enabled'] === true) {
                    try {
                        $banner_type_class = BannerType::where('title', $key)->first();
                    } catch(Exception $e) {
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Cannot retrieve banner type information'
                        ], 500);
                    }
                    foreach($banner_type['blocks'] as $key => $block) {
                        if($block['enabled'] === true) {
                            try {
                                $block_type = BlockType::findOrFail($block['block_type']['id']);
                            } catch(Exception $e) {
                                return response()->json([
                                    'status'    => 'error',
                                    'message'   => 'Cannot retrieve block type information'
                                ], 500);
                            }

                            unset($block['enabled']);
                            unset($block['block_type']);

                            $block['template_id'] = $template->id;
                            $block['banner_type_id'] = $banner_type_class->id;
                            $block['block_type_id'] = $block_type->id;

                            try {
                                $block_position = BlockPosition::create($block);
                            } catch(Exception $e) {
                                return response()->json([
                                    'status'    => 'error',
                                    'message'   => 'Cannot create new block position'
                                ], 500);
                            } 
                        }
                    }
                }
            }

            return response()->json([
                'status'    => 'success',
                'message'   => 'Created template'
            ], 200);
        } else {
            //Update
            $color_scheme = ColorScheme::findOrFail($request->input('id'));

            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }

            $color_scheme->title = $request->input('title');
            $color_scheme->background_color = $request->input('background_color');
            $color_scheme->cta_color = $request->input('cta_color');

            $color_scheme->save();
            return response()->json([
                'status'    => 'success',
                'message'   => 'Updated color scheme successfully'
            ], 200);
        }
    }
}
