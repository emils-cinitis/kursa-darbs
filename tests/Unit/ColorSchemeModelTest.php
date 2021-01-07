<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Banner;
use App\Models\ColorScheme;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use JWTAuth;

class ColorSchemeModelTest extends TestCase {
    //Create user
    private function createUser() {
        $password = Str::random(15);
        $email = Str::random(8) . '@' . Str::random(8);
        $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'username' => Str::random(40),
            'email' => $email,
            'password' => Hash::make($password),
            'user_role' => 2
        ]);

        return $user;
    }

    //Create color scheme for user
    private function createColorScheme($user_uuid) {
        $color_scheme = ColorScheme::create([
            'user_uuid' => $user_uuid,
            'title' => Str::random(40),
            'background_color' => '#FFFFFFFF',
            'text_color' => '#FFFFFFFF',
            'cta_color' => '#FFFFFFFF'
        ]);

        return $color_scheme;
    }

    //Create banner for color scheme
    private function createBanner($user_uuid, $color_scheme_id) {
        $uuid = Str::uuid()->toString();
        $banner = Banner::create([
            'uuid' => $uuid,
            'name' => Str::random(40),
            'link_url' => Str::random(40),
            'main_text' => Str::random(40),
            'sub_text' => Str::random(40),
            'image' => '/banners/' . $uuid . '/image.jpeg',
            'call_to_action' => Str::random(40), 
            'color_scheme_id' => $color_scheme_id,
            'template_id' => 1,
            'created_by' => $user_uuid
        ]);

        return $banner;
    }


    //Create color scheme
    public function test_create_color_scheme_success() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'title' => Str::random(40),
                'background_color' => '#FFFFFFFF',
                'text_color' => '#FFFFFFFF', 
                'cta_color' => '#FFFFFFFF'
            ]);

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'color_scheme' => ['id', 'title', 'background_color', 'text_color', 'cta_color']
            ]);
    }

    public function test_create_color_scheme_success_min() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'title' => Str::random(5),
                'background_color' => '#FFFFFFFF',
                'text_color' => '#FFFFFFFF', 
                'cta_color' => '#FFFFFFFF'
            ]);

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'color_scheme' => ['id', 'title', 'background_color', 'text_color', 'cta_color']
            ]);
    }

    public function test_create_color_scheme_success_max() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'title' => Str::random(255),
                'background_color' => '#FFFFFFFF',
                'text_color' => '#FFFFFFFF', 
                'cta_color' => '#FFFFFFFF'
            ]);

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'color_scheme' => ['id', 'title', 'background_color', 'text_color', 'cta_color']
            ]);
    }

    public function test_create_color_scheme_error_min() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'title' => Str::random(4),
                'background_color' => '#FFFFFFFF',
                'text_color' => '#FFFFFFFF', 
                'cta_color' => '#FFFFFFFF'
            ]);

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'messages' => [
                    'title' => ['The title must be at least 5 characters.']
                ]
                ], true);
    }

    public function test_create_color_scheme_error_max() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'title' => Str::random(256),
                'background_color' => '#FFFFFFFF',
                'text_color' => '#FFFFFFFF', 
                'cta_color' => '#FFFFFFFF'
            ]);

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'messages' => [
                    'title' => ['The title may not be greater than 255 characters.']
                ]
            ], true);
    }

    //Edit color scheme
    public function test_edit_color_scheme_success() {
        $user = $this->createUser();
        $color_scheme = $this->createColorScheme($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'id' => $color_scheme->id,
                'title' => Str::random(50),
                'background_color' => '#FFFF0000',
                'text_color' => '#FFFF0000', 
                'cta_color' => '#FFFF0000'
            ]);

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'color_scheme' => ['id', 'title', 'background_color', 'text_color', 'cta_color']
            ]);
    }

    public function test_edit_color_scheme_error_not_users() {
        $user = $this->createUser();
        $user_color_scheme_creator = $this->createUser();
        $color_scheme = $this->createColorScheme($user_color_scheme_creator->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'id' => $color_scheme->id,
                'title' => Str::random(50),
                'background_color' => '#FFFF0000',
                'text_color' => '#FFFF0000', 
                'cta_color' => '#FFFF0000'
            ]);

        $user_color_scheme_creator->deleteAllColorSchemes();
        $user_color_scheme_creator->delete();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Color scheme is not created by user'
            ], true);
    }

    public function test_edit_color_scheme_error_wrong_id() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/color-scheme', [
                'id' => 10000001,
                'title' => Str::random(50),
                'background_color' => '#FFFF0000',
                'text_color' => '#FFFF0000', 
                'cta_color' => '#FFFF0000'
            ]);

        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'No color scheme found with this ID'
            ], true);
    }

    //Delete color scheme
    public function test_delete_color_scheme_success() {
        $user = $this->createUser();
        $color_scheme = $this->createColorScheme($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/color-scheme', [ 'id' => $color_scheme->id ]);

        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Color scheme successfully deleted'], true);
    }

    public function test_delete_color_scheme_error_not_users() {
        $user = $this->createUser();
        $user_color_scheme_creator = $this->createUser();
        $color_scheme = $this->createColorScheme($user_color_scheme_creator->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/color-scheme', [ 'id' => $color_scheme->id ]);

        $user_color_scheme_creator->deleteAllColorSchemes();
        $user_color_scheme_creator->delete();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Color scheme is not created by user'
            ], true);
    }

    public function test_delete_color_scheme_error_has_banner() {
        $user = $this->createUser();
        $color_scheme = $this->createColorScheme($user->uuid);
        $banner = $this->createBanner($user->uuid, $color_scheme->id);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/color-scheme', [ 'id' => $color_scheme->id ]);

        $user->deleteAllBanners();
        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Color scheme is in use and cannot be deleted'
            ], true);
    }

    public function test_delete_color_scheme_error_empty_id() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/color-scheme', [ 'id' => 100000001 ]);

        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'No color scheme found with this ID'
            ], true);
    }

    //Get user color schemes
    public function test_get_color_schemes_success() {
        $user = $this->createUser();
        $color_scheme_1 = $this->createColorScheme($user->uuid);
        $color_scheme_2 = $this->createColorScheme($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-schemes');

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'color_schemes' => [
                    ['id', 'title', 'banners', 'background_color', 'text_color', 'cta_color']
                ]
            ]);
    }

    public function test_get_color_schemes_success_empty() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-schemes');

        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson([
                'color_schemes' => []
            ], true);
    }

    //Get usable color schemes
    public function test_get_usable_color_schemes_success() {
        $user = $this->createUser();
        $color_scheme_1 = $this->createColorScheme($user->uuid);
        $color_scheme_2 = $this->createColorScheme($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-schemes?input=true');

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'color_schemes' => [
                    ['value', 'text']
                ]
            ]);
    }

    public function test_get_usable_color_schemes_success_empty() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-schemes?input=true');

        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'color_schemes' => [
                    ['value', 'text']
                ]
            ]);
    }

    //Get full info
    public function test_get_color_scheme_info_success() {
        $user = $this->createUser();
        $color_scheme = $this->createColorScheme($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-scheme?id=' . $color_scheme->id );

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'color_scheme' => ['id', 'title', 'banners', 'background_color', 'text_color', 'cta_color']
            ]);
    }

    public function test_get_color_scheme_info_error_no_id() {
        $user = $this->createUser();
        $color_scheme = $this->createColorScheme($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-scheme' );

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Missing color scheme ID'
            ], true);
    }

    public function test_get_color_scheme_info_error_wrong_id() {
        $user = $this->createUser();
        $color_scheme = $this->createColorScheme($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-scheme/?id=' . ($color_scheme->id + 1) );

        $user->deleteAllColorSchemes();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'No color scheme found with this ID'
            ], true);
    }

    public function test_get_color_scheme_info_error_not_users() {
        $user = $this->createUser();
        $user_color_scheme_creator = $this->createUser();
        $color_scheme = $this->createColorScheme($user_color_scheme_creator->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/color-scheme/?id=' . $color_scheme->id );

        $user_color_scheme_creator->deleteAllColorSchemes();
        $user_color_scheme_creator->delete();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'message' => 'Color scheme is not created by user'
            ], true);
    }
}
