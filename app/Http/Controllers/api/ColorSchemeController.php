<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ColorScheme;


class ColorSchemeController extends Controller {

    private function getDefaultColorSchemes() {
        try {
            $color_schemes = ColorScheme::where('user_uuid', NULL)->get();
            return $color_schemes;
        } catch (\Exception $e) {
            return null;
        }
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
                $color_schemes = $user->colorSchemesSelect;
                $default_color_schemes = $this->getDefaultColorSchemes();
                if($default_color_schemes !== null) {
                    $color_schemes = array_merge(
                        $default_color_schemes->toArray(),
                        $color_schemes->toArray()
                    );
                }

                foreach($color_schemes as $key => $color_scheme) {
                    $color_scheme['value'] = $color_scheme['id'];
                    $color_scheme['text'] = $color_scheme['title'];
                    unset($color_scheme['id']);
                    unset($color_scheme['title']);
                    $color_schemes[$key] = $color_scheme;
                }
            } else {
                $color_schemes = $user->colorSchemes;
                $color_schemes_array = [];

                foreach($color_schemes as $color_scheme){
                    $banners = $color_scheme->banners()->get(['uuid', 'name']);

                    $color_scheme_array = array_merge(
                        $color_scheme->toArray(), 
                        [ 'banners' => $banners ]
                    );
                    array_push($color_schemes_array, $color_scheme_array);
                }

                $color_schemes = $color_schemes_array;
            }

            return response()->json([
                'status'        => 'success',
                'color_schemes' => $color_schemes
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user color schemes'
            ], 422);
        }
    }

    public function get(Request $request) {

        if(empty($request->input('id'))) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Missing color scheme ID'
            ], 422);
        }

        try {
            $color_scheme = ColorScheme::findOrFail($request->input('id'));
            $banners = $color_scheme->banners()->get(['uuid', 'name']);

            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }
            $color_scheme_array = $color_scheme = array_merge(
                $color_scheme->toArray(), 
                [ 'banners' => $banners ]
            );

            return response()->json([
                'status'        => 'success',
                'color_scheme'  => $color_scheme_array
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive color scheme information'
            ], 500);
        }
    }

    public function store(Request $request) {
        try {
            $user_uuid = Auth::user()->uuid;
        } catch (\Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'messages'  => ['unexpected' => 'Cannot retreive user information']
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'title'             => 'required|between:5,255',
            'background_color'  => 'required|regex:/#[0-9a-fA-F]{8}$/',
            'text_color'        => 'required|regex:/#[0-9a-fA-F]{8}$/',
            'cta_color'         => 'required|regex:/#[0-9a-fA-F]{8}$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => $validator->messages()
            ], 422);
        }

        try {
            if(empty($request->input('id'))) {
                //Create
                $color_scheme_variables = array_merge(
                    $request->all(), 
                    ['user_uuid' => $user_uuid] 
                );
                
                $color_scheme = ColorScheme::create($color_scheme_variables);

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Created color scheme successfully',
                    'color_scheme' => $color_scheme
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
                $color_scheme->text_color = $request->input('text_color');
                $color_scheme->cta_color = $request->input('cta_color');

                $color_scheme->save();
                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Updated color scheme successfully',
                    'color_scheme' => $color_scheme
                ], 200);
            }
        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'messages'  => ['unexpected' => 'Cannot save color scheme because of an unexpected error']
            ], 500);
        }
    }

    public function delete(Request $request) {
        try {
            $color_scheme = ColorScheme::findOrFail($request->input('id'));
            $banners = $color_scheme->banners;

            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }
            if(count($banners->toArray()) > 0) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is in use and cannot be deleted'
                ], 422);
            }

            $color_scheme->delete();

        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot delete color scheme because of an unexpected error'
            ], 500);
        }
    }
}