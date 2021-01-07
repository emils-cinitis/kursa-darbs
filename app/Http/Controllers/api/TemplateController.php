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
        //Check if template id exists
        if(empty($template_id)) {
            return [
                'error'     => true,
                'message'   => 'Missing template info'
            ];
        }

        //Find template model
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
                'message'   => 'No template found with this ID'
            ];
        }

        return $template;
    }

    private function getDefaultTemplates() {
        try {
            //Default template do not have a user who created them
            $templates = Template::where('user_uuid', NULL)->get(['id', 'title']);
            return $templates;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function getAllInfoFromTemplate(Template $template) {
        //Declare response variable as array
        $response_array = array();

        //Find blocks used by template
        $template_blocks = $template->blocks;

        //Cycle all blocks used by template and find both block types and banner types
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
            //Remove unnecessary id
            unset($block_array['template_id']);
            //Add extra variables to block
            $block_array['block_type'] = $block_type->title;
            $block_array['banner_type'] = $banner_type->title;
            
            //Add banner type key (e.g. Giga / Tower) if it doesn't exist
            if(!array_key_exists($banner_type->id, $response_array)) $response_array[$banner_type->id] = array();

            //Add block to correct banner type
            array_push($response_array[$banner_type->id], $block_array);
        }

        return $response_array;
    }

    private function rollback($elements, $edit = false) {
        //Reverse array, so last added elements are deleted first
        $elements = array_reverse($elements);
        $success = true;
        if(!$edit) {
            //Cycle all elements and delete them, even if one fails, continue
            foreach($elements as $key => $element) {
                try {
                    $element->delete();
                } catch (\Exception $e) {
                    $success = false;
                }
            }
        } else {
            //Cycle all elements and edit them to old data
            foreach($elements as $key => $element) {
                if(isset($element['model']) && isset($element['inputs'])) {
                //If element needs to be reverted
                    foreach($element['inputs'] as $model_key => $old_value) {
                        $element['model']->{$model_key} = $old_value;
                    }
                    try {
                        $element['model']->save();
                    } catch (\Exception $e) {
                        $success = false;
                    }

                } else {
                //If element needs to be deleted
                    try {
                        $element->delete();
                    } catch (\Exception $e) {
                        $success = false;
                    }
                }
            }
        }
        return $success;
    }

    public function getTemplateBlocks(Request $request) {
        //Get template and check if it belongs to user
        $template = $this->checkIfTemplateBelongsToUser($request->input('id'));
        //Return error if template is not user's
        if(!empty($template['error'])) {
            return response()->json([
                'status'    => 'error',
                'message'   => $template['message']
            ], 422);
        }

        //Find all blocks used in the template
        try {
            $blocks = $template->usedBlocks();
        } catch(\Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retrieve template info'
            ], 422);
        }

        //Find all banner types which this template supports
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
        //Find user model
        try {
            $user = User::find(Auth::user()->uuid);
        } catch (\Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ], 422);
        }

        try {
            //If used in dropdown input   
            if($request->input('input') == true) {
                //Get templates created by user and select only ID and Title
                $templates = $user->templatesSelect;

                //Get default templates
                $default_templates = $this->getDefaultTemplates();
                if($default_templates !== null) {
                    //Merge templates into one return array
                    $templates = array_merge(
                        $default_templates->toArray(),
                        $templates->toArray()
                    );
                }

                //Setup return array so VueJS Bootstrap can handle it
                foreach($templates as $key => $template) {
                    $template['value'] = $template['id'];
                    $template['text'] = $template['title'];
                    unset($template['id']);
                    unset($template['title']);
                    $templates[$key] = $template;
                }

            } else {
            //If in show all page for user
                //Get ordered user templates
                $templates = $user->templates()->orderBy('updated_at', 'DESC')->get();
                $templates_array = [];

                //Get each templtes banner type
                foreach($templates as $key => $template) {
                    $banner_types = $template->bannerTypes();
                    //Get banners used by template
                    $template->banners;
                    $template_array = $template->toArray();

                    //Remove blocks because unused
                    unset($template_array['blocks']);
                    
                    //Add extra variable to return item
                    $template_array['banner_types'] = $banner_types;

                    //Add return item to return array
                    $templates_array[$key] = $template_array;
                }
                //Change return array to template variable
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
        //Get template and check if it is created by this user
        $template = $this->checkIfTemplateBelongsToUser($request->input('id'));
        if(!empty($template['error'])) {
            return response()->json([
                'status'    => 'error',
                'message'   => $template['message']
            ], 422);
        }
        $banners = $template->banners()->get(['uuid', 'name']);

        $template = array_merge(
            $template->toArray(),
            [ 'banners' => $banners ]
        );

        return response()->json([
            'status'    => 'success',
            'template'  => $template
        ], 200);
    }

    public function store(Request $request) {
        //Find user UUID
        try {
            $user_uuid = Auth::user()->uuid;
        } catch (\Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ], 422);
        }

        //Validate title
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:5|max:255'
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
                'messages'  => ['banner_types' => ['No banner types specified']]
            ], 422);
        }

        //Check if any blocks are used in banner types
        $blocks_exist = [];
        foreach($banner_types as $name => $banner_type) {
            $blocks_exist[$name] = false;
            foreach($banner_type['blocks'] as $block) {
                if($block['enabled'] === true) {
                    $blocks_exist[$name] = true;
                    break;
                }
            }
        }

        //If one banner type has no blocks used return error
        foreach($blocks_exist as $exists) {
            if(!$exists) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => ['banner_types' => ['One or more banner types do not have active blocks']]
                ], 422);
            }
        }

        //Create new template
        if(empty($request->input('id'))) {
            $created_entities = [];

            //Add template to database
            try {
                $template_varaibles = [
                    'title' => $request->input('title'), 
                    'user_uuid' => $user_uuid 
                ];
                $template = Template::create($template_varaibles);
                //Add to rollbackable list
                array_push($created_entities, $template);

            } catch(\Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Cannot save template'
                ], 500);
            }

            //Create block positions
            foreach($banner_types as $key => $banner_type) {
                //If enabled, otherwise ignore
                if($banner_type['enabled'] === true) {

                    //Find banner type by title
                    try {
                        $banner_type_class = BannerType::where('title', $key)->first();
                        $banner_type_class->id;
                    } catch(\Exception $e) {
                        //Rollback created info
                        $success = $this->rollback($created_entities);
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Cannot retrieve banner type information',
                            'deleted'   => $success
                        ], 500);
                    }

                    //Cycle all blocks
                    foreach($banner_type['blocks'] as $key => $block) {
                        //If enabled, otherwise ignore
                        if($block['enabled'] === true) {
                            //Find block type by ID
                            try {
                                $block_type = BlockType::findOrFail($block['block_type']['id']);
                            } catch(\Exception $e) {
                                //Rollback created info
                                $success = $this->rollback($created_entities);
                                return response()->json([
                                    'status'    => 'error',
                                    'message'   => 'Cannot retrieve block type information',
                                    'deleted'   => $success
                                ], 500);
                            }

                            //Remove unused variables
                            unset($block['enabled']);
                            unset($block['block_type']);

                            //Set custom variables
                            $block['template_id'] = $template->id;
                            $block['banner_type_id'] = $banner_type_class->id;
                            $block['block_type_id'] = $block_type->id;

                            //Create block position in database
                            try {
                                $block_position = BlockPosition::create($block);
                                //Add to rollbackable list
                                array_push($created_entities, $block_position);
                            } catch(\Exception $e) {
                                //Rollback created info
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
                'message'   => 'Created template successfully',
                'id'        => $template->id
            ], 200);
        } else {
        //Update template

            $updated_entities = [];
            $deletable_entities = [];

            //Get template and check if it belongs to user
            $template = $this->checkIfTemplateBelongsToUser($request->input('id'));
            //Return error if template is not user's
            if(!empty($template['error'])) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => $template['message']
                ], 422);
            }

            //Update template title
            $old_title = $template->title;
            $template->title = $request->input('title');
            try {
                $template->save();
                //Add information about old title, if something fails later, rollback is doable
                array_push(
                    $updated_entities, 
                    [
                        'model' => $template, 
                        'inputs' => [
                            'title' => $old_title
                        ]
                    ]
                );
            } catch (\Exception $e) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Cannot save template title'
                ], 500);
            }


            foreach($banner_types as $key => $banner_type) {
                //Find banner type by title
                try {
                    $banner_type_class = BannerType::where('title', $key)->first();
                    $banner_type_class->id;
                } catch(\Exception $e) {
                    //Rollback created info
                    $success = $this->rollback($updated_entities, true);
                    return response()->json([
                        'status'    => 'error',
                        'message'   => 'Cannot retrieve banner type information',
                        'reverted'  => $success
                    ], 500);
                }

                //If enabled - update info
                if($banner_type['enabled'] === true) {
                    //If no blocks, remove updated info
                    if(count($banner_type['blocks']) == 0) {
                        $success = $this->rollback($updated_entities, true);
        
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Blocks missing for banner type(s)',
                            'reverted'  => $success
                        ], 422);
                    }

                    //Cycle all blocks
                    foreach($banner_type['blocks'] as $key => $block) {
                        //Find block type by ID
                        try {
                            $block_type = BlockType::findOrFail($block['block_type']['id']);
                        } catch(\Exception $e) {
                            //Rollback created info
                            $success = $this->rollback($updated_entities, true);
                            return response()->json([
                                'status'    => 'error',
                                'message'   => 'Cannot retrieve block type information',
                                'reverted'  => $success
                            ], 500);
                        }

                        //If enabled - create / edit
                        if($block['enabled'] === true) {
                            try {
                                $block_model = BlockPosition::where('template_id', '=', $template->id)
                                    ->where('banner_type_id', '=', $banner_type_class->id)
                                    ->where('block_type_id', '=', $block_type->id)
                                    ->first();
                                $block_exists = true;
                            } catch (\Exception $e) {
                                $block_exists = false;
                            }


                            if($block_exists && isset($block_model)) {
                                //Update existing block position
                                try {
                                    //Save old values
                                    $old_top_offset = $block_model->top_offset;
                                    $old_left_offset = $block_model->left_offset;
                                    $old_width = $block_model->width;
                                    $old_height = $block_model->height;
                                    $old_z_index = $block_model->z_index;

                                    //Set new values
                                    $block_model->top_offset = $block['top_offset'];
                                    $block_model->left_offset = $block['left_offset'];
                                    $block_model->width = $block['width'];
                                    $block_model->height = $block['height'];
                                    $block_model->z_index = $block['z_index'];

                                    //Add values to updated entity list
                                    array_push(
                                        $updated_entities, 
                                        [
                                            'model' => $block_model, 
                                            'inputs' => [
                                                'top_offset' => $old_title,
                                                'left_offset' => $old_left_offset,
                                                'width' => $old_width,
                                                'height' => $old_height,
                                                'z_index' => $old_z_index
                                            ]
                                        ]
                                    );

                                    //Save to DB
                                    $block_model->save();
                                } catch(\Exception $e) {
                                    $success = $this->rollback($updated_entities, true);
                                    return response()->json([
                                        'status'    => 'error',
                                        'message'   => 'Cannot update block position',
                                        'reverted'  => $success
                                    ], 500);
                                }
                            } else {
                                //Create block

                                //Remove unused variables
                                unset($block['enabled']);
                                unset($block['block_type']);

                                //Set custom variables
                                $block['template_id'] = $template->id;
                                $block['banner_type_id'] = $banner_type_class->id;
                                $block['block_type_id'] = $block_type->id;

                                //Create block position in database
                                try {
                                    $block_position = BlockPosition::create($block);
                                    //Add to rollbackable list
                                    array_push($updated_entities, $block_position);
                                } catch(\Exception $e) {
                                    //Rollback created info
                                    $success = $this->rollback($updated_entities, true);
                                    return response()->json([
                                        'status'    => 'error',
                                        'message'   => 'Cannot create new block position',
                                        'reverted'  => $success
                                    ], 500);
                                } 
                            }
                        } else {
                            //Delete old block position from DB
                            try {
                                $block_model = BlockPosition::where('template_id', $template->id)
                                    ->where('banner_type_id', $banner_type_class->id)
                                    ->where('block_type_id', $block_type->id)->first();

                                if(!empty($block_model)) {
                                    array_push($deletable_entities, $block_model);
                                }
                            } catch (\Exception $e) {
                                //Rollback created info
                                $success = $this->rollback($updated_entities, true);
                                return response()->json([
                                    'status'    => 'error',
                                    'message'   => 'Cannot retrieve block position',
                                    'reverted'  => $success
                                ], 500);
                            }
                        }
                    }
                } else {
                    //If disabled, delete info
                    try {
                        $block_model = BlockPosition::where('template_id', '=', $template->id)
                            ->where('banner_type_id', '=', $banner_type_class->id)->first();

                        if(!empty($block_model)) {
                            array_push($deletable_entities, $block_model);
                        }

                    } catch (\Exception $e) {
                        //Rollback created info
                        $success = $this->rollback($updated_entities, true);
                        return response()->json([
                            'status'    => 'error',
                            'message'   => 'Cannot retrieve block position',
                            'reverted'  => $success
                        ], 500);
                    }
                }
            }

            try {
                foreach($deletable_entities as $key => $entity) {
                    $entity->delete();
                }
            } catch (\Exception $e) {
                //Rollback created info
                $success = $this->rollback($updated_entities, true);
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Cannot delete block positions',
                    'reverted'  => $success
                ], 500);
            }

            return response()->json([
                'status'    => 'success',
                'message'   => 'Updated template successfully',
                'id'        => $template->id
            ], 200);
        }
    }

    public function delete(Request $request) {
        try {
            //Find template and it's banners and blocks
            $template = Template::findOrFail($request->input('id'));
            $banners = $template->banners;
            $blocks = $template->blocks;

            //Check if template is created by user
            if($template->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Template is not created by user'
                ], 422);
            }

            //Check if no banners are added to template
            if(count($banners->toArray()) > 0) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Template is in use and cannot be deleted'
                ], 422);
            }

        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot delete template because of an unexpected error'
            ], 500);
        }

        //Delete all template blocks
        try {
            foreach($blocks as $block) {
                $block->delete();
            }
        } catch(\Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot delete template blocks because of an unexpected error'
            ], 500);
        }

        //Delete template
        try {
            $template->delete();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Template successfully deleted'
            ], 200);

        } catch(\Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot delete template because of an unexpected error'
            ], 500);
        }
        
    }
}
