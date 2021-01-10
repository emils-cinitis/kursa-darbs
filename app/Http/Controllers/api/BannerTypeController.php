<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BannerType;

class BannerTypeController extends Controller {

    /**
     * Get all banner types from database
     * 
     * @return Response JSON response with success or error
     */
    public function get() {
        try {
            //Get all banner types from database
            $banner_types = BannerType::all();

            $banner_types_reformatted = [];
            //Change array to be sorted by title not ID
            foreach($banner_types as $key => $banner_type) {
                $banner_type_title = strtolower($banner_type['title']);
                unset($banner_type['title']);
                $banner_types_reformatted[$banner_type_title] = $banner_type;
            }

            return response()->json([
                'status'        => 'success', 
                'banner_types'  => $banner_types_reformatted
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get banner types'
            ], 500);
        }
    }

}