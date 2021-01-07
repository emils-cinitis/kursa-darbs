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
            //Default color schemes have no user who created them
            $color_schemes = ColorScheme::where('user_uuid', NULL)->get();
            return $color_schemes;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAll(Request $request) {
        try {
            //Find user model
            $user = User::find(Auth::user()->uuid);
        } catch (\Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ], 422);
        }

        try {
            //If requested for input fields then add default color schemes
            if($request->input('input') == true) {
                //Get user created color schemes
                $color_schemes = $user->colorSchemesSelect;
                //Get default color schemes
                $default_color_schemes = $this->getDefaultColorSchemes();
                //If there are default color schemes, merge user created and default color schemes
                if($default_color_schemes !== null) {
                    $color_schemes = array_merge(
                        $default_color_schemes->toArray(),
                        $color_schemes->toArray()
                    );
                }

                //Setup return so it can be used with VueJS Bootstrap
                foreach($color_schemes as $key => $color_scheme) {
                    $color_scheme['value'] = $color_scheme['id'];
                    $color_scheme['text'] = $color_scheme['title'];
                    unset($color_scheme['id']);
                    unset($color_scheme['title']);
                    $color_schemes[$key] = $color_scheme;
                }
            } else {
            //If requested for user show all page
                //Get user created color schemes
                $color_schemes = $user->colorSchemes;
                $color_schemes_array = [];

                //Cycle all color schemes and add used banner info to them
                foreach($color_schemes as $color_scheme){
                    //Get only UUID and name as nothing else is needed
                    $banners = $color_scheme->banners()->get(['uuid', 'name']);

                    //Merge into one array
                    $color_scheme_array = array_merge(
                        $color_scheme->toArray(), 
                        [ 'banners' => $banners ]
                    );
                    //Push to return array
                    array_push($color_schemes_array, $color_scheme_array);
                }

                //Change return array to different variable to be returned
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
        //Check if there is an ID given
        if(empty($request->input('id'))) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Missing color scheme ID'
            ], 422);
        }

        //Find color scheme by ID
        try {
            $color_scheme = ColorScheme::findOrFail($request->input('id'));
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'No color scheme found with this ID'
            ], 422);
        }

        try{
            //Find all color scheme banner's UUID and name
            $banners = $color_scheme->banners()->get(['uuid', 'name']);

            //Check if user created this color scheme
            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }

            //Merge color schemes and reduced banner info to one array
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
        //Check if user is authenticated
        try {
            $user_uuid = Auth::user()->uuid;
        } catch (\Exception $e) {
            
            return response()->json([
                'status'    => 'error',
                'messages'  => ['unexpected' => 'Cannot retreive user information']
            ], 422);
        }

        //Validate given inputs
        $validator = Validator::make($request->all(), [
            'title'             => 'required|min:5|max:255',
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

        //Create new color scheme
        if(empty($request->input('id'))) {
            //Add user uuid to already passed variables
            $color_scheme_variables = array_merge(
                $request->all(), 
                ['user_uuid' => $user_uuid] 
            );
            
            //Create in database
            try {
                $color_scheme = ColorScheme::create($color_scheme_variables);
            } catch(\Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'messages'  => ['unexpected' => 'Cannot save color scheme because of an unexpected error']
                ], 500);
            }

            //Return with full info about color scheme
            return response()->json([
                'status'    => 'success',
                'message'   => 'Color scheme created successfully',
                'color_scheme' => $color_scheme
            ], 200);
        } else {
        //Update existing color scheme
            //Find color scheme by ID
            try {
                $color_scheme = ColorScheme::findOrFail($request->input('id'));
            } catch(\Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'No color scheme found with this ID'
                ], 422);
            }

            //Check if created by user
            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }

            //Change all fields with new inputs
            $color_scheme->title = $request->input('title');
            $color_scheme->background_color = $request->input('background_color');
            $color_scheme->text_color = $request->input('text_color');
            $color_scheme->cta_color = $request->input('cta_color');

            //Save changes to DB
            try {
                $color_scheme->save();
            } catch(\Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'messages'  => ['unexpected' => 'Cannot save color scheme because of an unexpected error']
                ], 500);
            }

            //Return with full info about color scheme
            return response()->json([
                'status'    => 'success',
                'message'   => 'Color scheme edited successfully',
                'color_scheme' => $color_scheme
            ], 200);
        }
    }

    public function delete(Request $request) {
        try {
            //Find color scheme by ID and banners that use this color scheme
            $color_scheme = ColorScheme::findOrFail($request->input('id'));
        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'No color scheme found with this ID'
            ], 422);
        }

        try{
            $banners = $color_scheme->banners;

            //Check if color scheme is created by user
            if($color_scheme->user_uuid !== Auth::user()->uuid) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is not created by user'
                ], 422);
            }

            //Check if no banners use this color scheme
            if(count($banners->toArray()) > 0) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Color scheme is in use and cannot be deleted'
                ], 422);
            }

            //Delete color scheme from database
            $color_scheme->delete();

            return response()->json([
                'status'    => 'success',
                'message'   => 'Color scheme successfully deleted'
            ], 200);

        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot delete color scheme because of an unexpected error'
            ], 500);
        }
    }
}