<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use JWTAuth;

class UserModelTest extends TestCase {

    private function createArrayForApi($email, $uuid = null, $password_length = 15, $name_length = 15) {
        $password = Str::random($password_length);
        $info = [
            'username' => Str::random($name_length),
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ];
        if($uuid != null) $info['uuid'] = $uuid;
        return $info;
    }

    private function createUser($email, $name_length, $password) {
        return User::create([
            'uuid' => Str::uuid()->toString(),
            'username' => Str::random($name_length),
            'email' => $email,
            'password' => Hash::make($password),
            'user_role' => 2
        ]);
    }
    
    /* Registration testing */
    public function test_register_success_standart() {
        $email = Str::random(10) . '@' . Str::random(5);
        $response = $this->postJson('/api/user/store', $this->createArrayForApi($email) );

        $user = User::where('email', $email)->first()->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'User registered successfully' ], true);
    }

    public function test_register_success_minimum() {
        $email = Str::random(10) . '@' . Str::random(5);
        $response = $this->postJson('/api/user/store', $this->createArrayForApi($email, null, 8, 5) );

        $user = User::where('email', $email)->first()->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'User registered successfully' ], true);
    }

    public function test_register_success_maximum() {
        $email = Str::random(99) . '@' . Str::random(155);
        $response = $this->postJson('/api/user/store', $this->createArrayForApi($email, null, 255, 255));

        $user = User::where('email', $email)->first()->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'User registered successfully' ], true);
    }

    public function test_register_error_password() {
        $response = $this->postJson('/api/user/store', [
            'username' => 'Emils', 
            'email' => 'emis_2@inbox.lv',
            'password' => 'Parole1234',
            'password_confirmation' => 'Parole123'
        ]);

        $response->assertStatus(422)->assertJson([ 'messages' => [ 'password' => ['The password confirmation does not match.'] ] ], true);
    }

    public function test_register_error_length_long() {
        $response = $this->postJson('/api/user/store', $this->createArrayForApi(Str::random(99) . '@' . Str::random(156), null, 256, 256));

        $response->assertStatus(422)
            ->assertJson(['messages' => [
                'username'  => ['The username may not be greater than 255 characters.'],
                'email'     => ['The email may not be greater than 255 characters.'], 
                'password'  => ['The password may not be greater than 255 characters.']
            ] ], true);
    }

    public function test_register_error_length_short() {
        $password = Str::random(7);

        $response = $this->postJson('/api/user/store', $this->createArrayForApi(Str::random(2) . '@' . Str::random(2), null, 7, 4));

        $response->assertStatus(422)
            ->assertJson([ 'messages' => [
                'username' => ['The username must be at least 5 characters.'], 
                'password' => ['The password must be at least 8 characters.']
            ] ], true);
    }

    public function test_register_error_unique() {
        $password = Str::random(8);

        $response = $this->postJson('/api/user/store', [
            'username' => 'admin', 
            'email' => 'admin@admin',
            'password' => $password,
            'password_confirmation' => $password
        ]);

        $response->assertStatus(422)
            ->assertJson(['messages' => [
                'username' => ['The username has already been taken.'],
                'email' => ['The email has already been taken.']
            ] ], true);
    }

    public function test_register_error_missing_data() {
        $response = $this->postJson('/api/user/store');
        $response->assertStatus(422)
            ->assertJson([ 'messages' => [
                'username' => ['The username field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ] ], true);
    }


    /* Login testing */
    public function test_login_correct() {
        $response = $this->postJson('/api/user/login', ['email' => 'admin@admin', 'password' => 'adminadmin' ]);
        $response->assertStatus(200)->assertHeader('Authorization');
    }

    public function test_login_incorrect() {
        $response = $this->postJson('/api/user/login', [ 'email' => '1@1', 'password' => '1' ]);
        $response->assertStatus(422)->assertJson([ 'message' => 'Email and / or password is incorrect' ], true);
    }

    public function test_login_missing_data() {
        $response = $this->postJson('/api/user/login');
        $response->assertStatus(422)
            ->assertJson([ 'messages' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.']
            ] ], true);
    }


    /* Logout function */
    public function test_logout_success() {
        $user = User::where('email', 'admin@admin')->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/user/logout');
        $response->assertStatus(200)->assertJson(['message' => 'Logged out successfully'], true);
    }

    public function test_logout_error() {
        $response = $this->postJson('/api/user/logout');
        $response->assertStatus(401)->assertJson(['error' => 'Unauthorized'], true);
    }

    /* User full info */
    public function test_user_full_info() {
        $user = User::where('email', 'admin@admin')->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/all');
        $response->assertJson(['status' => 'success', 'data' => [
            'email' => 'admin@admin',
            'username' => 'admin',
            'user_role' => 2
        ] ], true);
    }

    /* Delete */
    public function test_user_delete_success() {
        $user = $this->createUser('test@test', 40, 'testtest');
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->deleteJson('/api/user/delete', ['password' => 'testtest']);
        $response->assertStatus(200)->assertJson([ 'message' => 'User successfully deleted' ], true);
    }

    public function test_user_delete_missing_password() {
        $user = User::where('email', 'admin@admin')->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->deleteJson('/api/user/delete');
        $response->assertStatus(422)->assertJson(['message' => 'The password field is required.'], true);
    }

    public function test_user_delete_wrong_password() {
        $user = User::where('email', 'admin@admin')->first();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->deleteJson('/api/user/delete', ['password' => 'wrong']);
        $response->assertStatus(422)->assertJson(['message' => 'Password is incorrect'], true);
    }

    /* User edit */
    public function test_user_edit_success_without_password() {
        //Create
        $user = $this->createUser('test_user_1@test', 40, 'testtest');
        $token = JWTAuth::fromUser($user);

        //Edit
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/store', ['username' => Str::random(40), 'email' => 'test1@test1']);

        //Delete
        $user->delete();
        $response->assertStatus(200)->assertJson(['message' => 'User edited successfully'], true);
    }

    public function test_user_edit_success_with_password() {
        //Create
        $user = $this->createUser('test@test', 40, 'testtest');
        $token = JWTAuth::fromUser($user);

        //Edit
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/store', [ 'username' => Str::random(40), 'email' => 'test1@test1', 'password' => 'test1test1', 'password_confirmation' => 'test1test1' ]);
                        
        //Delete
        $user->delete();
        $response->assertStatus(200)->assertJson(['message' => 'User edited successfully'], true);
    }

    public function test_user_edit_error_unique() {
        //Create
        $user = $this->createUser('test_user_1@test', 40, 'testtest');
        $token = JWTAuth::fromUser($user);

        //Edit
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/store', ['username' => 'admin', 'email' => 'admin@admin' ]);
        
        //Delete
        $user->delete();

        $response->assertJson(['messages' => [
            'username' => ['The username has already been taken.'],
            'email' => ['The email has already been taken.']
        ] ], true);
    }
}
