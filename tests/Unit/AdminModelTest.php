<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Banner;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use JWTAuth;

class AdminModelTest extends TestCase {
    // Create user
    private function createUser($username, $email, $password, $role = 2) {
        return User::create([
            'uuid' => Str::uuid()->toString(),
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'user_role' => $role
        ]);
    }

    //Create banner
    private function createBanner($user) {
        $uuid = Str::uuid()->toString();
        return Banner::create([
            'uuid' => $uuid,
            'name' => Str::random(40),
            'link_url' => Str::random(40),
            'main_text' => Str::random(40),
            'sub_text' => Str::random(40),
            'image' => '/banners/' . $uuid . '/image.jpeg',
            'call_to_action' => Str::random(40), 
            'color_scheme_id' => 1,
            'template_id' => 1,
            'created_by' => $user->uuid
        ]);
    }

    /* Get all users */
    public function test_get_users_success() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/admin/users');
        
        $admin->delete();

        $response->assertStatus(200)
            ->assertJsonStructure([ 'users' => [ [ 'uuid', 'username', 'email', 'user_role', 'color_scheme_count', 'template_count', 'banner_count' ] ] ]);
    }

    public function test_get_users_with_filters_success() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/admin/users/' . '?name=' . $admin->username . '&email=' . $admin->email . '&role=' . $admin->user_role . '&color_scheme_min=' . 0 . 
            '&color_scheme_max=' . 0 . '&template_min=' . 0 . '&template_max=' . 0 . '&banner_min=' . 0 . '&banner_max=' . 0 );
        
        $admin->delete();

        $response->assertStatus(200)
            ->assertJsonStructure([ 'users' => [ ['uuid', 'username', 'email', 'user_role', 'color_scheme_count', 'template_count', 'banner_count'] ] ]);
    }

    public function test_get_users_with_filters_return_none() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/admin/users/'. '?name=' . $admin->username . '&email=' . $admin->email . '&role=' . $admin->user_role . '&color_scheme_min=' . 1 .
                '&color_scheme_max=' . 0 . '&template_min=' . 1 . '&template_max=' . 0 . '&banner_min=' . 1 . '&banner_max=' . 0 );
        
        $admin->delete();

        $response->assertStatus(200)->assertJson([ 'users' => [] ], true);
    }

    public function test_get_users_no_permission() {
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/admin/users');
        
        //Delete user
        $user->delete();

        $response->assertStatus(403)->assertJson(['error' => 'Unauthorized']);
    }

    //Specific user view
    public function test_get_user_success() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/admin/user/?uuid=' . $user->uuid);
        
        $user->delete();
        $admin->delete();

        $response->assertStatus(200)
            ->assertJsonStructure([ 'user' => [ 'uuid', 'username', 'email', 'user_role', 'color_scheme_count', 'template_count', 'banner_count' ] ]);
    }

    //User roles
    public function test_change_user_role_success() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/admin/user/', [ 'uuid' => $user->uuid, 'role' => 1 ]);
        
        $user->delete();
        $admin->delete();

        $response->assertStatus(200)
            ->assertJsonStructure([ 'message', 'user' => [ 'username', 'email', 'user_role' ] ]);
    }

    public function test_change_user_role_error_wrong_role() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/admin/user/', [ 'uuid' => $user->uuid, 'role' => 10 ]);
        
        $user->delete();
        $admin->delete();

        $response->assertStatus(500)->assertJson([ 'message' => 'Cannot change user role' ], true);
    }

    public function test_change_user_role_error_wrong_uuid() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/admin/user/', [ 'uuid' => $user->uuid . '1', 'role' => 1 ]);
        
        $user->delete();
        $admin->delete();

        $response->assertStatus(500)->assertJson([ 'message' => 'Cannot change user role' ], true);
    }

    public function test_change_user_role_error_missing_data() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/admin/user/');
        
        $user->delete();
        $admin->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No user UUID passed' ], true);
    }

    //Delete user
    public function test_delete_user_success() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/admin/user/', [ 'uuid' => $user->uuid ]);

        $admin->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'User successfully deleted' ], true);
    }

    public function test_delete_user_error_wrong_uuid() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $user = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15));

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/admin/user/', ['uuid' => $user->uuid . '1']);

        $admin->delete();
        $user->delete();

        $response->assertStatus(500)->assertJson([ 'message' => 'Cannot delete user info' ], true);
    }

    //Get banners
    public function test_get_banners() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/admin/banners');

        $admin->delete();

        $response->assertStatus(200)
            ->assertJsonStructure([ 'banners' => [ [
                'banner_types', 'call_to_action', 'color_scheme', 'created_at', 'image', 'link_url',
                'main_text', 'name', 'sub_text', 'template', 'updated_at', 'user_uuid']
                ], 'next_page' ]);
    }

    public function test_get_banners_with_filters() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $banner = $this->createBanner($admin);

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/admin/banners' . '?title=' . $banner->name . '&user_uuid=' . $admin->uuid );

        $banner->delete();
        $admin->delete();

        $response->assertStatus(200)
            ->assertJsonStructure([ 'banners' => [ [ 
                'banner_types', 'call_to_action', 'color_scheme', 'created_at', 'image', 'link_url',
                 'main_text', 'name', 'sub_text', 'template', 'updated_at', 'user_uuid']
                ], 'next_page' ]);
    }

    public function test_get_banners_with_filters_return_none() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $banner = $this->createBanner($admin);

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/admin/banners' . '?title=' . $banner->name . '&user_uuid=1' . $admin->uuid );

        $banner->delete();
        $admin->delete();

        $response->assertStatus(200)->assertJson([ 'banners' => [], 'next_page' => false ], true);
    }

    //Delete banner
    public function test_delete_banner_success() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $banner = $this->createBanner($admin);

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->deleteJson('/api/admin/banner', [ 'uuid' => $banner->uuid ] );

        $admin->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'Banner successfully deleted' ], true);
    }

    public function test_delete_banner_error_wrong_uuid() {
        $admin = $this->createUser(Str::random(40), Str::random(8) . '@' . Str::random(8), Str::random(15), 3);
        $banner = $this->createBanner($admin);

        $token = JWTAuth::fromUser($admin);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/admin/banner');

        $banner->delete();
        $admin->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No banner UUID passed' ], true);
    }
}
