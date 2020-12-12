<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\UserRoles;
use JWTAuth;

class UserController extends Controller {

    /**
     * Saves user info
     */
    public function store(Request $request) {
        $user_info = $request->all();
        
        
        if(Auth::user() == null){
            //Create user
            $validator = Validator::make($request->all(), [
                'username'  => 'required|unique:users',
                'email'     => 'required|unique:users|email',
                'password'  => 'required|confirmed',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => $validator->messages()
                ], 422);
            }
            
            $user_info['uuid']= Str::uuid()->toString();
            if(!isset($user_info['user_role'])) $user_info['user_role'] = 2;
            $user_info['password'] = Hash::make($request->password);

            try {
                $user = User::create($user_info);

                return response()->json(
                    [
                        'status'    => 'success', 
                        'message'   => 'Registered successfully'
                    ], 200
                );

            } catch(\Exception $e) {

                return response()->json(
                    [
                        'status'    => 'error', 
                        'message'   => 'Cannot register user'
                    ], 500
                );

            }
        } else {
            //Edit user
            $user = User::find(Auth::user()->uuid);

            $validatableData = [
                'username'  => 'required',
                'email'     => 'required|email',
            ];

            if($request->input('username') != $user->username) $validatableData['username'] = 'required|unique:users';
            if($request->input('email') != $user->email) $validatableData['email'] = 'required|email|unique:users';
            if(!empty($request->input('password'))) array_push($validatableData, ['password'  => 'required|confirmed']);

            $validator = Validator::make($request->all(), $validatableData);
            if ($validator->fails()) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => $validator->messages()
                ], 422);
            }

            $user->username = $request->input('username');
            $user->email = $request->input('email');

            if(!empty($request->input('password'))){
                $user->password = Hash::make($request->password);
            }

            try {
                $user->save();

                return response()->json(
                    [
                        'status'    => 'success', 
                        'message'   => 'Edited successfully'
                    ], 200
                );

            } catch(\Exception $e) {

                return response()->json(
                    [
                        'status'    => 'error', 
                        'message'   => 'Cannot edit user'
                    ], 500
                );
            }
        }
    }


    public function delete(Request $request){
        try {
            $user = User::find(Auth::user()->uuid);

            $user->deleteAllBanners();
            $user->delete();

            return response()->json([
                'status'    => 'success', 
                'message'   => 'User deleted'
            ], 200);

        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error', 
                'message'   => 'Cannot delete user'
            ], 500);
        }
    }


    /**
     * Login user and return a token
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email and / or password is wrong'
            ], 422);
        }
        return response()->json(['status' => 'successs'], 200)
            ->header('Access-Control-Expose-Headers', 'Authorization')
            ->header('Authorization', $token);
    }

    

    /**
     * Logout User
     */
    public function logout() {
        try {
            auth()->logout();
            
            return response([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ], 200);

        } catch (\Exception $e) {

            return response([
                'status' => 'error',
                'message' => 'Cannot logout user'
            ], 200);
        }
    }


    /**
     * Get authenticated user
     */
    public function get(Request $request) {
        try {
            $user = User::find(Auth::user()->uuid);
            return response()->json([
                'status'    => 'success',
                'data'      => $user
            ]);

        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ]);
        }
    }

    /**
     * Refresh JWT token
     */
    public function refresh() {
        try {
            if ($token = $this->guard()->refresh()) {
                return response()
                    ->json(['status' => 'successs'], 200)
                    ->header('Access-Control-Expose-Headers', 'Authorization')
                    ->header('Authorization', $token);
            }
        } catch(\Exception $e) {
            return response()->json(['error' => 'refresh_token_error'], 401);
        }
        return response()->json(['error' => 'refresh_token_error'], 401);
    }

    /**
     * Return auth guard
     */
    private function guard() {
        return Auth::guard();
    }
}
