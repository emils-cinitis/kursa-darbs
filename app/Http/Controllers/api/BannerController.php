<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Banner;
use App\Models\User;

class BannerController extends Controller {
    public function getAll() {
        $user = User::find(Auth::user()->uuid);
        try {
            $banners = $user->banners;
            return response()->json([
                'status' => 'success', 
                'banners' => $banners
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get banners from user'
            ], 500);
        }
    }

    public function get(Request $request) {
        $uuid = $request->input('uuid');
        
        if(!empty($uuid)) {
            try {
                $banner = Banner::find($uuid);
                return response()->json([
                    'status' => 'success',
                    'banner' => $banner
                ], 200);

            } catch(Exception $e) {

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

    public function store(Request $request){
        $validatedData = $request->validate([
            'name'     => 'required',
            'main_text'  => 'required',
        ]);

        if(empty($request->input('id'))){
            //Create banner
            try {
                $extra_variables = [
                    'id' => Str::uuid()->toString(),
                    'created_by' => Auth::user()->uuid
                ];
                $banner_variables = array_merge($extra_variables, $request->all());
                $banner = Banner::create($banner_variables);
                return response()->json([
                    'status'  => 'success', 
                    'message' => 'Created successfully'
                ], 200);
            } catch(Exception $e) {
                return response()->json([
                    'status'  => 'error', 
                    'message' => 'Cannot create banner'
                ], 500);
            }
        } else {
            //Edit banner
        }
    }
}
