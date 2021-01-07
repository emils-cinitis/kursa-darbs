<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Banner;
use App\Models\UserRoles;

class AdminController extends Controller {
    private function getAllUserInfo(User $user) {
        //Get user role object
        $user->role;

        //Read all counts of created items
        $banner_count = $user->banners()->get()->count();
        $color_scheme_count = $user->colorSchemes()->get()->count();
        $template_count = $user->templates()->get()->count();

        //Create array with additional values
        $user_array = array_merge(
            $user->toArray(),
            [
                'uuid'                  => $user->uuid,
                'banner_count'          => $banner_count,
                'color_scheme_count'    => $color_scheme_count,
                'template_count'        => $template_count
            ]
        );

        return $user_array;
    }

    public function getUsers(Request $request) {
        try {
            $users_query = User::query();
            $max_users_per_page = 20;
            $users_array = [];

            //Default fields
            if(!empty($request->input('name'))) {
                $users_query->where('username', 'LIKE', '%' . $request->input('name') . '%');
            }
            if(!empty($request->input('email'))) {
                $users_query->where('email', 'LIKE', '%' . $request->input('email') . '%');
            }
            if(!empty($request->input('role'))) {
                $users_query->where('user_role', $request->input('role'));
            }

            //Number fields
            if(!empty($request->input('color_scheme_min'))) {
                $users_query->has('colorSchemes', '>=', $request->input('color_scheme_min'));
            }
            if(!is_null($request->input('color_scheme_max')) && $request->input('color_scheme_max') == 0) {
                $users_query->has('colorSchemes', '=', '0');
            } else if(!empty($request->input('color_scheme_max'))) {
                $users_query->has('colorSchemes', '<=', $request->input('color_scheme_max'));
            }
            if(!empty($request->input('template_min'))) {
                $users_query->has('templates', '>=', $request->input('template_min'));
            }
            if(!is_null($request->input('template_max')) && $request->input('template_max') == 0) {
                $users_query->has('templates', '<=', '0');
            } else if(!empty($request->input('template_max'))){
                $users_query->has('templates', '<=', $request->input('template_max'));
            }
            if(!empty($request->input('banner_min'))) {
                $users_query->has('banners', '>=', $request->input('banner_min'));
            }
            if(!is_null($request->input('banner_max')) && $request->input('banner_max') == 0) {
                $users_query->has('banners', '<=', '0');
            } else if(!empty($request->input('banner_max'))) {
                $users_query->has('banners', '<=', $request->input('banner_max'));
            }

            $page = 1;
            if(!empty($request->input('page'))) {
                $page = $request->input('page');
            }

            $users_query->paginate($max_users_per_page, ['*'], 'page', $page);
            $users = $users_query->get();

            foreach($users as $user) {
                $user_array = $this->getAllUserInfo($user);
                array_push($users_array, $user_array);
            }

            $has_next_page = (User::all()->count() / $max_users_per_page) > $page;

            return response()->json([
                'status'    => 'success', 
                'users'     => $users_array,
                'next_page' => $has_next_page
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get user list'
            ], 500);
        }
    }

    public function getUser(Request $request) {
        if(!empty($request->input('uuid'))) {
            try {
                $user = User::findOrFail($request->input('uuid'));
                $user_array = $this->getAllUserInfo($user);

                return response()->json([
                    'status'    => 'success', 
                    'user'      => $user_array
                ], 200);

            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot get user info'
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'No user UUID passed'
            ], 422);
        }
    }

    public function getUserRoles(Request $request) {
        try {
            $user_roles = UserRoles::all();
            $user_roles_formatted = [];

            foreach($user_roles as $role) {
                array_push(
                    $user_roles_formatted,
                    [
                        'value' => $role->id,
                        'text'  => $role->role
                    ]
                );
            }

            return response()->json([
                'status'     => 'success', 
                'user_roles' => $user_roles_formatted
            ], 200);

        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get user role info'
            ], 500);
        }
    }

    public function changeUserRole(Request $request) {
        if(!empty($request->input('uuid')) && !empty($request->input('role'))) {
            try {
                $user = User::findOrFail($request->input('uuid'));
                $user->user_role = $request->input('role');

                $user->save();

                return response()->json([
                    'status'    => 'success', 
                    'message'   => 'User role successfully edited',
                    'user'      => $user
                ], 200);

            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot change user role'
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'No user UUID passed'
            ], 422);
        }
    }

    public function deleteUser(Request $request) {
        if(!empty($request->input('uuid'))) {
            try {
                $user = User::findOrFail($request->input('uuid'));

                $user->deleteAllBanners();
                $user->deleteAllColorSchemes();
                $user->deleteAllTemplates();
                
                $user->delete();

                return response()->json([
                    'status'    => 'success', 
                    'message'   => 'User successfully deleted'
                ], 200);

            } catch(\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot delete user info'
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'No user UUID passed'
            ], 422);
        }
    }

    public function getBanners(Request $request) {
        try {
            $banner_query = Banner::query();
            $banners_array = [];
            $max_banners_per_page = 20;

            //Filters
            if(!empty($request->input('title'))) {
                $banner_query->where('name', 'like', '%' . $request->input('title') . '%');
            }
            if(!empty($request->input('user_uuid'))) {
                $banner_query->where('created_by', $request->input('user_uuid'));
            }

            //Set current page
            $page = 1;
            if(!empty($request->input('page'))) {
                $page = $request->input('page');
            }

            $banner_query->paginate($max_banners_per_page, ['*'], 'page', $page);
            $banners = $banner_query->get();

            foreach($banners as $banner) {
                //Get banner types
                $banner_types = $banner->template->bannerTypes();

                //Get color scheme
                $banner->colorScheme;

                //Get user
                $user = $banner->user;

                array_push(
                    $banners_array,
                    array_merge(
                        $banner->toArray(),
                        [
                            'banner_types'  => $banner_types,
                            'user_uuid'     => $user->uuid
                        ]
                    )
                );
            }

            $has_next_page = (Banner::all()->count() / $max_banners_per_page) > $page;

            return response()->json([
                'status'    => 'success', 
                'banners'   => $banners_array,
                'next_page' => $has_next_page
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Cannot get banner list'
            ], 500);
        }
    }

    public function deleteBanner(Request $request) {
        if(!empty($request->input('uuid'))) {
            try {
                $banner = Banner::findOrFail($request->input('uuid'));
                $banner->delete();

                return response()->json([
                    'status'    => 'success', 
                    'message'   => 'Banner successfully deleted'
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error', 
                    'message' => 'Cannot delete banner'
                ], 500);
            }
        } else {
            return response()->json([
                'status' => 'error', 
                'message' => 'No banner UUID passed'
            ], 422);
        }
    }
}
