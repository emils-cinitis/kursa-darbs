<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

use App\Models\User;
use App\Models\UserRoles;
use App\Models\PasswordReset;
use App\Mail\PasswordReset as PasswordResetMail;
use JWTAuth;
use Carbon\Carbon;

class UserController extends Controller {

    /**
     * Store users' information to database
     * 
     * @param Request $request Request containing user information
     * 
     * @return Response JSON response with success or error
     */
    public function store(Request $request) {
        $user_info = $request->all();
        
        
        if(Auth::user() == null){
            //Create user
            $validator = Validator::make($request->all(), [
                'username'  => 'required|min:5|max:255|unique:users',
                'email'     => 'required|max:255|unique:users|email',
                'password'  => 'required|min:8|max:255|confirmed',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => $validator->messages()
                ], 422);
            }
            
            $user_info['uuid'] = Str::uuid()->toString();
            if(!isset($user_info['user_role'])) $user_info['user_role'] = 2;
            $user_info['password'] = Hash::make($request->password);

            try {
                $user = User::create($user_info);

                return response()->json(
                    [
                        'status'    => 'success', 
                        'message'   => 'User registered successfully'
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
                'username'  => 'required|min:5|max:255',
                'email'     => 'required|max:255|email',
            ];

            if($request->input('username') != $user->username) $validatableData['username'] = 'required|min:5|max:255|unique:users';
            if($request->input('email') != $user->email) $validatableData['email'] = 'required|max:255|email|unique:users';
            if(!empty($request->input('password'))) $validatableData['password'] = 'required|min:8|max:255|confirmed';

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
                        'message'   => 'User edited successfully'
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

    /**
     * Delete user from database
     * 
     * @param Request $request Request containing users' password
     * 
     * @return Response JSON response with success or error
     */
    public function delete(Request $request){
        try {
            $user = User::find(Auth::user()->uuid);

            if(empty($request->input('password'))) {
                return response()->json([
                    'status'    => 'error', 
                    'message'   => 'The password field is required.'
                ], 422);
            }
            if(!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'    => 'error', 
                    'message'   => 'Password is incorrect'
                ], 422);
            }

            $user->deleteAllBanners();
            $user->delete();

            return response()->json([
                'status'    => 'success', 
                'message'   => 'User successfully deleted'
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
     * 
     * @param Request $request containing users' email and password
     * 
     * @return Response JSON response with success headers or error
     */
    public function login(Request $request) {
        $validator = Validator::make(
            $request->only('email', 'password'), 
            [
                'email' => 'required',
                'password' => 'required'
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                'status'    => 'error', 
                'messages'  => $validator->messages()
            ], 422);
        }

        $credentials = $request->only('email', 'password');
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email and / or password is incorrect'
            ], 422);
        }
        return response()->json(['status' => 'successs'], 200)
            ->header('Access-Control-Expose-Headers', 'Authorization')
            ->header('Authorization', $token);
    }

    
    /**
     * Logout user from system
     * 
     * @return Response JSON response with success or error
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
     * Get authenticated users information
     * 
     * @param Request $request Request containing banner UUID
     * 
     * @return Response JSON response with success or error
     */
    public function get() {
        try {
            $user = User::find(Auth::user()->uuid);
            return response()->json([
                'status'    => 'success',
                'data'      => $user
            ], 200);

        } catch(\Exception $e) {

            return response()->json([
                'status'    => 'error',
                'message'   => 'Cannot retreive user information'
            ], 422);
        }
    }

    /**
     * Send password reset email
     * 
     * @param Request $request Request containing user email
     * 
     * @return Response JSON response with success or error
     */
    public function resetPasswordEmail(Request $request) {
        if(!empty($request->input('email'))) {
            try {
                $user = User::where('email', $request->input('email'))->first();
                $uuid = $user->uuid;
                $user->deleteAllPasswordResets();

            } catch(\Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'No user found with email'
                ], 422);
            }

            try {
                $password_reset = PasswordReset::create([
                    'uuid' => Str::uuid()->toString(),
                    'created_at' => Carbon::now(),
                    'user_uuid' => $uuid
                ]);

                Mail::to($user->email)->send(new PasswordResetMail($password_reset->uuid));

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'Password reset email sent!'
                ], 200);
            } catch(\Exception $e) {

                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Failed to send reset email'
                ], 500);
            }

        } else {
            return response()->json([
                'status'    => 'error',
                'message'   => 'The email field is required.'
            ], 422);
        }
    }

    /**
     * Reset password confirmation
     * 
     * @param Request $request Request containing password reset UUID, user password
     * 
     * @return Response JSON response with success or error
     */
    public function resetPassword(Request $request) {
        if(!empty($request->input('uuid'))) {
            $validator = Validator::make($request->all(), [
                'password' => 'required|min:8|max:255|confirmed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'    => 'error', 
                    'messages'  => $validator->messages()
                ], 422);
            }

            $reset_uuid = $request->input('uuid');
            try {
                $reset_request = PasswordReset::findOrFail($reset_uuid);
                $user = $reset_request->user;
            } catch(\Exception $e) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'No password reset found with UUID'
                ], 422);
            }

            try {
                $user->password = Hash::make($request->input('password'));
                $user->save();
                $reset_request->delete();

                return response()->json([
                    'status'    => 'success',
                    'message'   => 'User edited successfully'
                ], 200);

            } catch(\Exception $e) {
                return response()->json([
                    'status'    => 'error',
                    'message'   => 'Cannot edit user'
                ], 500);
            }
        } else {
            return response()->json([
                'status'    => 'error',
                'message'   => 'No password reset UUID passed'
            ], 422);
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
