<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Banner;
use App\Models\Template;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use JWTAuth;

class BannerModelTest extends TestCase {

    //Create user
    private function createUser() {
        return $user = User::create([
            'uuid' => Str::uuid()->toString(),
            'username' => Str::random(40),
            'email' => Str::random(8) . '@' . Str::random(8),
            'password' => Hash::make(Str::random(15)),
            'user_role' => 2
        ]);
    }

    //Create template for user
    private function createTemplate($user_uuid) {
        return $template = Template::create([
            'user_uuid' => $user_uuid,
            'title' => Str::random(40)
        ]);
    }

    //Create banner for color scheme
    private function createBanner($user_uuid) {
        $uuid = Str::uuid()->toString();
        return $banner = Banner::create([
            'uuid' => $uuid,
            'name' => Str::random(40),
            'link_url' => Str::random(40),
            'main_text' => Str::random(40),
            'sub_text' => Str::random(40),
            'image' => '/banners/' . $uuid . '/image.jpeg',
            'call_to_action' => Str::random(40), 
            'color_scheme_id' => 1,
            'template_id' => 1,
            'created_by' => $user_uuid
        ]);
    }

    //Create array to pass to API
    private function createArrayForBannerCreation($color_scheme_id = 1, $template_id = 1, $title_size = 40, $text_size = 40, $image = null, $uuid = null) {
        if($image == null) $image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAAJCAYAAADpeqZqAAAAAXNSR0IArs4c6QAAADhlWElmTU0AKgAAAAgAAYdpAAQAAAABAAAAGgAAAAAAAqACAAQAAAABAAAADaADAAQAAAABAAAACQAAAAB/gpxUAAAAfklEQVQYGYWPQQqAIBBFvUfBuOgYXbxdi4JuYVCLbmFvxIHCoT48lT8+qRCc5JwFFuidcVtV4WTXJPgWuSBgAseSxOqLDARMSJxHOECT4C1SCDyFcoEuQitSCjSC/S2zVqScQbPD+xOqST/ABZopsAhsEO11b2eu4gqdN//tbqicz8K0aFl4AAAAAElFTkSuQmCC';
        $url_size = ($text_size > 8) ? $text_size - 8 : $text_size;
        $info = [
            'name' => Str::random($title_size),
            'main_text' => Str::random($text_size),
            'sub_text' => Str::random($text_size),
            'call_to_action' => Str::random($text_size),
            'link_url' => 'https://' . Str::random($url_size),
            'image' => $image,
            'color_scheme_id' => $color_scheme_id,
            'template_id' => $template_id 
        ];
        if($uuid != null) $info['uuid'] = $uuid;
        return $info;
    }

    //Create banner
    public function test_create_banner_success() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation());

        $user->deleteAllBanners();
        $user->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'Banner created successfully' ], true);
    }

    public function test_create_banner_success_min() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 5, 1));

        $user->deleteAllBanners();
        $user->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'Banner created successfully' ], true);
    }

    public function test_create_banner_success_max() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 255, 255));

        $user->deleteAllBanners();
        $user->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'Banner created successfully' ], true);
    }

    public function test_create_banner_error_min() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 4, 0));

        $user->delete();

        $response->assertStatus(422)
            ->assertJson([ 'messages' => [
                'name' => ['The name must be at least 5 characters.'],
                'link_url' => ['The link url must be at least 9 characters.'] ] ], true);
    }

    public function test_create_banner_error_max() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 256, 256));

        $user->delete();

        $response->assertStatus(422)
            ->assertJson([
                'messages' => [
                    'name' => ['The name may not be greater than 255 characters.'],
                    'main_text' => ['The main text may not be greater than 255 characters.'],
                    'sub_text' => ['The sub text may not be greater than 255 characters.'],
                    'call_to_action' => ['The call to action may not be greater than 255 characters.'],
                    'link_url' => ['The link url may not be greater than 255 characters.']
            ] ], true);
    }

    public function test_create_banner_error_empty() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->postJson('/api/user/banners');

        $user->delete();

        $response->assertStatus(422)
            ->assertJson([ 'messages' => [
                    'color_scheme_id' => ['The color scheme id field is required.'],
                    'template_id' => ['The template id field is required.']
            ] ], true);
    }

    public function test_create_banner_error_wrong_color_scheme() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(9999999, 1, 5, 1));

        $user->delete();

        $response->assertStatus(422)
            ->assertJson([ 'messages' => [ 'color_scheme_id' => ['No color scheme with this ID'] ]], true);
    }

    public function test_create_banner_error_wrong_template() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 9999999, 5, 1));

        $user->delete();

        $response->assertStatus(422)
            ->assertJson([ 'messages' => [ 'template_id' => ['No template with this ID'] ]], true);
    }

    public function test_create_banner_error_image_format() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 40, 40, '123'));

        $user->delete();

        $response->assertStatus(422)
            ->assertJson([ 'messages' => [ 'image' => ['Image is in wrong format'] ]], true);
    }

    //Edit banner
    public function test_edit_banner_success() {
        $user = $this->createUser();
        $banner = $this->createBanner($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 40, 40, null, $banner->uuid));

        $user->deleteAllBanners();
        $user->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'Banner edited successfully' ], true);
    }

    public function test_edit_banner_error_not_users() {
        $user = $this->createUser();
        $user_for_banner = $this->createUser();
        $banner = $this->createBanner($user_for_banner->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 40, 40, null, $banner->uuid));

        $user_for_banner->deleteAllBanners();
        $user_for_banner->delete();
        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'Banner not created by user' ], true);
    }

    public function test_edit_banner_error_wrong_uuid() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/banners', $this->createArrayForBannerCreation(1, 1, 40, 40, null, '1'));
            
        $user->delete();

        $response->assertStatus(500)->assertJson([ 'message' => 'Cannot find banner by UUID' ], true);
    }

    //Delete
    public function test_delete_banner_success() {
        $user = $this->createUser();
        $banner = $this->createBanner($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/banner', ['uuid' => $banner->uuid]);
            
        $user->delete();

        $response->assertStatus(200)->assertJson([ 'message' => 'Banner deleted successfully' ], true);
    }

    public function test_delete_banner_error_no_uuid() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->deleteJson('/api/user/banner');
            
        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No banner UUID passed' ], true);
    }

    public function test_delete_banner_error_wrong_uuid() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/banner', [ 'uuid' => '1' ]);
            
        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No banner with this UUID' ], true);
    }

    public function test_delete_banner_error_not_users() {
        $user = $this->createUser();
        $user_banner_creator = $this->createUser();
        $banner = $this->createBanner($user_banner_creator->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/banner', ['uuid' => $banner->uuid]);
            
        $user_banner_creator->deleteAllBanners();
        $user_banner_creator->delete();
        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => "Banner not created by user" ], true);
    }

    //Get full banner info
    public function test_get_banner_info_success() {
        $user = $this->createUser();
        $banner = $this->createBanner($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/banner/?uuid=' . $banner->uuid);

        $user->deleteAllBanners();
        $user->delete();

        $response->assertStatus(200)
            ->assertJsonStructure(['banner' => [
                    'uuid', 'name', 'link_url', 'main_text', 'sub_text', 'call_to_action', 'image', 'template_id', 'color_scheme_id'
            ] ]);
    }

    public function test_get_banner_info_error_no_uuid() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/banner/');

        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No banner UUID passed' ], true);
    }

    public function test_get_banner_info_error_wrong_uuid() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/banner/?uuid=1');

        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No banner found with UUID' ], true);
    }

    public function test_get_banner_info_error_not_users() {
        $user = $this->createUser();
        $user_banner_creator = $this->createUser();
        $banner = $this->createBanner($user_banner_creator->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/banner/?uuid=' . $banner->uuid);

        $user_banner_creator->deleteAllBanners();
        $user_banner_creator->delete();
        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'Banner not created by user' ], true);
    }

    //Get user banner list
    public function test_get_banner_list_success() {
        $user = $this->createUser();
        $banner = $this->createBanner($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/banners/');

        $user->deleteAllBanners();
        $user->delete();

        $response->assertStatus(200)
            ->assertJsonStructure([ 'banners' => [
                    ['uuid', 'name', 'link_url', 'main_text', 'sub_text', 'call_to_action', 'image', 'banner_types', 'color_scheme']
            ] ]);
    }

    public function test_get_banner_list_success_empty() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/banners/');

        $user->delete();

        $response->assertStatus(200)->assertJson([ 'banners' => [] ], true);
    }

    //Get public banner full info
    public function test_get_public_banner_info_success() {
        $user = $this->createUser();
        $user_banner_creator = $this->createUser();
        $banner = $this->createBanner($user_banner_creator->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/banner/?uuid=' . $banner->uuid);

        $user_banner_creator->deleteAllBanners();
        $user_banner_creator->delete();
        $user->delete();

        $response->assertStatus(200)
            ->assertJsonStructure(['banner' => [
                    'uuid', 'name', 'link_url', 'main_text', 'sub_text', 'call_to_action', 'image', 'created_by_name', 'color_scheme', 'banner_types'
            ] ]);
    }

    public function test_get_public_banner_info_error_no_uuid() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/banner/');

        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No banner UUID passed' ], true);
    }

    public function test_get_public_banner_info_error_wrong_uuid() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/user/banner/?uuid=1');

        $user->delete();

        $response->assertStatus(422)->assertJson([ 'message' => 'No banner found with UUID' ], true);
    }
}
