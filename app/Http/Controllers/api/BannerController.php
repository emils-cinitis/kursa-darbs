<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Banner;
use App\Models\User;
use App\Models\ColorScheme;
use App\Models\Template;

class BannerController extends Controller {
    public function getAll() {
        $return_array = [];
        try {
            $user = User::find(Auth::user()->uuid);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get user'
            ], 500);
        }

        try {
            $banners = $user->banners()->orderBy('updated_at', 'DESC')->get();
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get banners from user'
            ], 500);
        }

        foreach($banners as $banner) {
            $banner_array = $banner->toArray();
            try {
                $banner_types = $banner->template->bannerTypes();
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner types'
                ], 500);
            }

            try {
                $color_scheme = $banner->colorScheme;
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner color scheme'
                ], 500);
            }

            unset($banner_array['template_id']);
            unset($banner_array['color_scheme_id']);

            array_push($return_array, array_merge(
                $banner_array,
                [
                    'banner_types' => $banner_types,
                    'color_scheme' => $color_scheme
                ]
            ));
        }

        return response()->json([
            'status' => 'success', 
            'banners' => $return_array
        ], 200);
    }

    public function get(Request $request) {
        $uuid = $request->input('uuid');
        
        if(!empty($uuid)) {
            try {
                $banner = Banner::findOrFail($uuid);
                return response()->json([
                    'status' => 'success',
                    'banner' => $banner
                ], 200);

            } catch(\Exception $e) {

                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot get banner information'
                ], 500);
            }
        } else { 
            return response()->json([
                'status'  => 'error', 
                'message' => 'No banner UUID passed'
            ], 422);
        }
    }

    public function getPublicBanner(Request $request) {
        $uuid = $request->input('uuid');
        
        if(!empty($uuid)) {
            //Find banner
            try {
                $banner = Banner::findOrFail($uuid);
            } catch(\Exception $e) {

                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot get banner information'
                ], 500);
            }

            //Find user name who created banner
            try {
                $created_user = $banner->user->username;
            } catch(\Exception $e) {
                $created_user = NULL;
            }

            //Find banner types
            try {
                $banner_types = $banner->template->bannerBlocksByTypes();
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner types'
                ], 500);
            }

            //Find color scheme info
            try {
                $banner->colorScheme;
            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get banner color scheme'
                ], 500);
            }

            //Remove unused information
            $banner_modal_array = $banner->toArray();
            unset($banner_modal_array['color_scheme_id']);
            unset($banner_modal_array['template_id']);
            unset($banner_modal_array['template']);
            unset($banner_modal_array['user']);

            //Create array to return
            $banner_array = array_merge(
                $banner_modal_array,
                [ 
                    'created_by_name' => $created_user,
                    'banner_types' => $banner_types,
                ]
            );


            return response()->json([
                'status' => 'success',
                'banner' => $banner_array
            ], 200);
        } else { 
            return response()->json([
                'status'  => 'error', 
                'message' => 'No banner UUID passed'
            ], 422);
        }
    }

    public function store(Request $request) {
        //Get user UUID
        try {
            $user_uuid = Auth::user()->uuid;
        } catch(\Exception $e) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => 'No user information'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'color_scheme_id'   => 'required|gt:0',
            'template_id'       => 'required|gt:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => $validator->messages()
            ], 422);
        }

        //Check if color scheme can be used by user
        $color_scheme_id = $request->input('color_scheme_id');
        try {
            $color_scheme = ColorScheme::findOrFail($color_scheme_id);

            if(!($color_scheme->user_uuid === null || $color_scheme->user_uuid == $user_uuid)) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => ['color_scheme_id' => ['Color scheme doesn`t belong to user']]
                ], 422);
            }

        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => ['color_scheme_id' => ['No color scheme with this ID']]
            ], 422);
        }

        //Check if template can be used by user
        $template_id = $request->input('template_id');
        try {
            $template = Template::findOrFail($template_id);
            
            if(!($template->user_uuid === null || $template->user_uuid == $user_uuid)) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => ['template_id' => ['Template doesn`t belong to user']]
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => ['template_id' => ['No template with this ID']]
            ], 422);
        }

        //Validate requested inputs
        $validation_array = ['name' => 'required'];
        $template_blocks = array_keys($template->usedBlocks());
        foreach($template_blocks as $block) {
            $validation_array[$block] = 'required';
        }
        
        $validator = Validator::make($request->all(), $validation_array);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => $validator->messages()
            ], 422);
        }


        if(empty($request->input('uuid'))){
            //Create banner
            try {
                $extra_variables = [
                    'uuid' => Str::uuid()->toString(),
                    'created_by' => $user_uuid
                ];

                $banner_variables = array_merge(
                    $extra_variables, 
                    $request->only('name', 'main_text', 'sub_text', 'call_to_action', 'color_scheme_id', 'template_id')
                );

                $banner = Banner::create($banner_variables);
                return response()->json([
                    'status'  => 'success', 
                    'message' => 'Created successfully'
                ], 200);
            } catch(\Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot create banner'
                ], 500);
            }
        } else {
            //Edit banner
            try {
                $banner = Banner::findOrFail($request->input('uuid'));
            } catch(\Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot find banner by UUID'
                ], 500);
            }

            $banner->name = $request->input('name');
            $banner->main_text = $request->input('main_text');
            $banner->sub_text = $request->input('sub_text');
            $banner->image = $request->input('image');
            $banner->call_to_action = $request->input('call_to_action');
            $banner->color_scheme_id = $color_scheme_id;
            $banner->template_id = $template_id;
            try {
                $banner->save();
                return response()->json([
                    'status'  => 'success', 
                    'message' => 'Edited successfully'
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot edit banner'
                ], 500);
            }
        }
    }
}
