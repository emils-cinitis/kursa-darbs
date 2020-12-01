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
            $color_schemes = ColorScheme::where('user_uuid', NULL)->get(['id','title']);
            return $color_schemes;
        } catch (Exception $e) {
            return null;
        }
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
            }

            return response()->json([
                'status'        => 'success',
                'color_schemes' => $color_schemes
            ], 200);
        } catch (Exception $e) {

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
                'message'   => 'Missing color scheme info'
            ], 422);
        }

        try {
            $color_scheme = ColorScheme::findOrFail($request->input('id'));

            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }

            return response()->json([
                'status'        => 'success',
                'color_scheme'  => $color_scheme
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive color scheme'
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
            'title'             => 'required',
            'background_color'  => 'required',
            'text_color'        => 'required',
            'cta_color'         => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'message'   => $validator->messages()
            ], 422);
        }

        try {
            if(empty($request->input('id'))) {
                //Create
                $color_scheme_variables = array_merge($request->all(), ['user_uuid' => $user_uuid] );
                $color_scheme = ColorScheme::create($color_scheme_variables);

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Created color scheme successfully'
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
                    'message'   => 'Updated color scheme successfully'
                ], 200);
            }
        } catch(Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot save color scheme'
            ], 422);
        }
    }
}