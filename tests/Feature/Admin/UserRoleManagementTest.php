<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserRoleManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // نصب جدول‌ها + ساخت نقش‌ها/مجوزها
        $this->artisan('migrate');
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_super_admin_can_create_user_with_roles()
    {
        $super = User::factory()->create();
        $super->assignRole('super-admin');

        $this->actingAs($super);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Ali',
            'email' => 'ali@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['admin'],
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', ['email'=>'ali@example.com']);
        $this->assertTrue(User::whereEmail('ali@example.com')->first()->hasRole('admin'));
    }

    public function test_non_super_admin_cannot_access_user_management()
    {
        $user = User::factory()->create(); // بدون نقش

        $this->actingAs($user);
        $this->get(route('admin.users.index'))->assertForbidden();
    }
}
