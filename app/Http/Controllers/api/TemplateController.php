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

    private function checkIfTemplateBelongsToUser($template_id) {
        if(empty($template_id)) {
            return [
                'error'     => true,
                'message'   => 'Missing template info'
            ];
        }

        try {
            $template = Template::findOrFail($template_id);

            if(!($template->user_uuid === null || $template->user_uuid === Auth::user()->uuid)) {
                return [
                    'error'     => true,
                    'message'   => 'Template is not created by user'
                ];
            }
        } catch (\Exception $e) {
            return [
                'error'     => true,
                'message'   => 'No template with this ID'
            ];
        }

        return $template;
    }

    private function getDefaultTemplates() {
        try {
            $templates = Template::where('user_uuid', NULL)->get(['id', 'title']);
            return $templates;
        } catch (\Exception $e) {
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
                
            } catch(\Exception $e) {

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

    public function getTemplateBlocks(Request $request) {
        $template = $this->checkIfTemplateBelongsToUser($request->input('id'));
        if(!empty($template['error'])) {
            return response()->json([
                'status'    => 'error',
                'message'   => $template['message']
            ], 422);
        }

        try {
            $blocks = $template->usedBlocks();
        } catch(\Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retrieve template info'
            ], 422);
        }

        try {
            $banner_types = $template->bannerBlocksByTypes();
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get banner types'
            ], 500);
        }

        return response()->json([
            'status'   => 'success',
            'position' => $banner_types,
            'enabled'  => $blocks
        ], 200);
    }

    public function getAll(Request $request) {
        try {
            $user = User::find(Auth::user()->uuid);
        } catch (\Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ], 422);
        }

        try {            
            if($request->input('input') == true) {
                $templates = $user->templatesSelect;

                $default_templates = $this->getDefaultTemplates();
                if($default_templates !== null) {
                    $templates = array_merge(
                        $default_templates->toArray(),
                        $templates->toArray()
                    );
                }

                foreach($templates as $key => $template) {
                    $template['value'] = $template['id'];
                    $template['text'] = $template['title'];
                    unset($template['id']);
                    unset($template['title']);
                    $templates[$key] = $template;
                }

            } else {
                //Get user templates
                $templates = $user->templates()->orderBy('updated_at', 'DESC')->get();
                $templates_array = [];

                //Get each templtes banner type
                foreach($templates as $key => $template) {
                    $banner_types = $template->bannerTypes();
                    //Get banners used by template
                    $template->banners;
                    $template_array = $template->toArray();

                    $template_array['banner_count'] = count($template_array['banners']);

                    //Remove blocks because unused
                    unset($template_array['blocks']);
                    //unset($template_array['banners']);
                    
                    $template_array['banner_types'] = $banner_types;

                    $templates_array[$key] = $template_array;
                }
                $templates = $templates_array;
            }

            return response()->json([
                'status'    => 'success',
                'templates' => $templates
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user templates'
            ], 500);
        }
    }

    public function get(Request $request) {
        $template = $this->checkIfTemplateBelongsToUser($request->input('id'));
        if(!empty($template['error'])) {
            return response()->json([
                'status'    => 'error',
                'message'   => $template['message']
            ], 422);
        }

        $template_info = $this->getAllInfoFromTemplate($template);

        return response()->json([
            'status'    => 'success',
            'template'  => $template_info
        ], 200);
    }

    private function rollback($elements) {
        $elements = array_reverse($elements);
        $success = true;
        foreach($elements as $key => $element) {
            try {
                $element->delete();
            } catch (\Exception $e) {
                $success = false;
            }
        }
        return $success;
    }

    public function store(Request $request) {
        try {
            $user_uuid = Auth::user()->uuid;
        } catch (\Exception $e) {
            
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
                'messages'  => $validator->messages()
            ], 422);
        }

        //Check if there are any enabled banner types
        $banner_types = $request->input('banner_types');
        $enabled_banner_types = 0;
        foreach($banner_types as $banner_type){
            if($banner_type['enabled'] === true) {
                $enabled_banner_types++;
            break;
            }
        }
        if($enabled_banner_types == 0) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => ['banner_types' => ['No banner types specified!']]
            ], 422);
        }

        if(empty($request->input('id'))) {
            //Create template

            $created_entities = [];
            try {
                $template_varaibles = [
                    'title' => $request->input('title'), 
                    'user_uuid' => $user_uuid 
                ];
                $template = Template::create($template_varaibles);
                array_push($created_entities, $template);

            } catch(\Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Cannot save template'
                ], 500);
            }

            //Create block positions
            foreach($banner_types as $key => $banner_type) {
                if($banner_type['enabled'] === true) {
                    try {
                        $banner_type_class = BannerType::where('title', $key)->first();
                    } catch(\Exception $e) {

                        $success = $this->rollback($created_entities);
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Cannot retrieve banner type information',
                            'deleted'   => $success
                        ], 500);
                    }
                    foreach($banner_type['blocks'] as $key => $block) {
                        if($block['enabled'] === true) {
                            try {
                                $block_type = BlockType::findOrFail($block['block_type']['id']);
                            } catch(\Exception $e) {

                                $success = $this->rollback($created_entities);
                                return response()->json([
                                    'status'    => 'error',
                                    'message'   => 'Cannot retrieve block type information',
                                    'deleted'   => $success
                                ], 500);
                            }

                            unset($block['enabled']);
                            unset($block['block_type']);

                            $block['template_id'] = $template->id;
                            $block['banner_type_id'] = $banner_type_class->id;
                            $block['block_type_id'] = $block_type->id;

                            try {
                                $block_position = BlockPosition::create($block);
                                array_push($created_entities, $block_position);
                            } catch(\Exception $e) {

                                $success = $this->rollback($created_entities);
                                return response()->json([
                                    'status'    => 'error',
                                    'message'   => 'Cannot create new block position',
                                    'deleted'   => $success
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
            /*$color_scheme = ColorScheme::findOrFail($request->input('id'));

            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }

            $color_scheme->title = $request->input('title');
            $color_scheme->background_color = $request->input('background_color');
            $color_scheme->cta_color = $request->input('cta_color');

            $color_scheme->save();*/
            return response()->json([
                'status'    => 'success',
                'message'   => 'Updated color scheme successfully'
            ], 200);
        }
    }
}
