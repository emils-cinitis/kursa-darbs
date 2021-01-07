<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Banner;
use App\Models\BlockPosition;
use App\Models\Template;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

use JWTAuth;

class TemplateModelTest extends TestCase {
    
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

    //Create template for user
    private function createTemplate($user_uuid, $with_block = false) {
        $template = Template::create([
            'user_uuid' => $user_uuid,
            'title' => Str::random(40)
        ]);

        if($with_block) {
            BlockPosition::create([
                'width' => 1,
                'height' => 1,
                'left_offset' => 1,
                'top_offset' => 1,
                'z_index' => 1,
                'template_id' => $template->id,
                'banner_type_id' => 1,
                'block_type_id' => 1
            ]);
        }

        return $template;
    }

    //Create banner for color scheme
    private function createBanner($user_uuid, $template_id) {
        $uuid = Str::uuid()->toString();
        $banner = Banner::create([
            'uuid' => $uuid,
            'name' => Str::random(40),
            'link_url' => Str::random(40),
            'main_text' => Str::random(40),
            'sub_text' => Str::random(40),
            'image' => '/banners/' . $uuid . '/image.jpeg',
            'call_to_action' => Str::random(40), 
            'color_scheme_id' => 1,
            'template_id' => $template_id,
            'created_by' => $user_uuid
        ]);

        return $banner;
    }

    //Create template
    public function test_create_template_success() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(40),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Created template successfully'], true);
    }

    public function test_create_template_success_tilte_min() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(5),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Created template successfully'], true);
    }

    public function test_create_template_success_tilte_max() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(255),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Created template successfully'], true);
    }

    public function test_create_template_error_tilte_min() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(4),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['messages' => ['title' => ['The title must be at least 5 characters.']]], true);
    }

    public function test_create_template_error_tilte_max() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(256),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['messages' => ['title' => ['The title may not be greater than 255 characters.']]], true);
    }

    public function test_create_template_error_banner_type() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(40),
                'banner_types' => [
                    'giga1' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->delete();

        $response
            ->assertStatus(500)
            ->assertJson(['message' => 'Cannot retrieve banner type information'], true);
    }

    public function test_create_template_error_no_enabled_banner_type() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(40),
                'banner_types' => [
                    'giga' => [
                        'enabled' => false,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'messages' => [
                    'banner_types' => ['No banner types specified']
                ]
            ], true);
    }

    public function test_create_template_error_block_id() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(40),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 7,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->delete();

        $response
            ->assertStatus(500)
            ->assertJson(['message' => 'Cannot retrieve block type information'], true);
    }

    public function test_create_template_error_no_enabled_blocks() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'title' => Str::random(40),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => false,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson([
                'messages' => [
                    'banner_types' => [
                        'One or more banner types do not have active blocks'
                    ]
                ]
            ], true);
    }

    //Edit template
    public function test_edit_template_success() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'id' => $template->id,
                'title' => Str::random(40),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Updated template successfully'], true);
    }

    public function test_edit_template_error_not_users() {
        $user = $this->createUser();
        $template_user = $this->createUser();
        $template = $this->createTemplate($template_user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'id' => $template->id,
                'title' => Str::random(40),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $template_user->deleteAllTemplates();
        $template_user->delete();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['message' => 'Template is not created by user'], true);
    }

    public function test_edit_template_error_wrong_id() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/user/template', [
                'id' => ($template->id + 1),
                'title' => Str::random(40),
                'banner_types' => [
                    'giga' => [
                        'enabled' => true,
                        'blocks' => [
                            [
                                'enabled' => true,
                                'width' => 1,
                                'height' => 1,
                                'left_offset' => 1,
                                'top_offset' => 1,
                                'z_index' => 1,
                                'block_type' => [
                                    'id' => 1,
                                    'title' => 'main_text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['message' => 'No template found with this ID'], true);
    }

    //Delete template
    public function test_delete_template_success() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid, true);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/template', ['id' => $template->id]);

        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Template successfully deleted'], true);
    }

    public function test_delete_template_error_has_items() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid, true);
        $banner = $this->createBanner($user->uuid, $template->id);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/user/template', ['id' => $template->id]);

        $user->deleteAllBanners();
        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['message' => 'Template is in use and cannot be deleted'], true);
    }

    //Get user banner templates
    public function test_get_user_templates_success() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid, true);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/templates');

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'templates' => [
                    ['id', 'title', 'banners', 'banner_types']
                ]
            ]);
    }

    public function test_get_user_templates_success_empty() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/templates');

        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJson([
                'templates' => [ ]
            ], true);
    }

    //Get template full info
    public function test_get_user_template_success() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid, true);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/template?id=' . $template->id);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'template' => [
                    'id', 'title', 'banners'
                ]
            ]);
    }

    public function test_get_user_template_error_no_id() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/template');

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['message' => 'Missing template info'], true);
    }

    public function test_get_user_template_error_wrong_id() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/template?id=' . ($template->id + 1));

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['message' => 'No template found with this ID'], true);
    }

    public function test_get_user_template_error_not_users() {
        $user = $this->createUser();
        $user_template_creator = $this->createUser();
        $template = $this->createTemplate($user_template_creator->uuid, true);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/template?id=' . $template->id);

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(422)
            ->assertJson(['message' => 'Template is not created by user'], true);
    }

    //Get usable templates
    public function test_get_user_usable_templates_success() {
        $user = $this->createUser();
        $template = $this->createTemplate($user->uuid, true);

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/templates?input=true');

        $user->deleteAllTemplates();
        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'templates' => [
                    ['value', 'text']
                ]
            ]);
    }

    public function test_get_user_usable_templates_success_empty() {
        $user = $this->createUser();

        $token = JWTAuth::fromUser($user);
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/user/templates?input=true');

        $user->delete();

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'templates' => [
                    ['value', 'text']
                ]
            ]);
    }
}
