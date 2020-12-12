<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BannerType;

class BannerTypeController extends Controller {
    public function get(Request $request) {
        try {
            $banner_types = BannerType::all();

            $banner_types_reformatted = [];
            foreach($banner_types as $key => $banner_type) {
                $banner_type_title = strtolower($banner_type['title']);
                unset($banner_type['title']);
                $banner_types_reformatted[$banner_type_title] = $banner_type;
            }

            return response()->json([
                'status'        => 'success', 
                'banner_types'  => $banner_types_reformatted
            ], 200);
        } catch(Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get banner types'
            ], 500);
        }
    }

}