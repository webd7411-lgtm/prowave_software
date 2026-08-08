<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(DepartmentSeeder::class);
        $this->seed(SettingsSeeder::class);
        $this->seed(AdminUserSeeder::class);
    }

    public function test_admin_login_and_inactive_user_blocked(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@prowave.com',
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('dashboard');

        $user = User::where('email', 'admin@prowave.com')->first();
        $this->assertSame(UserStatus::Active->value, $user->status->value);

        auth()->logout();
        session()->flush();

        $inactiveUser = User::factory()->create([
            'email' => 'inactive@prowave.com',
            'status' => UserStatus::Inactive->value,
            'password' => Hash::make('password'),
        ]);
        $inactiveUser->assignRole('Developer');

        $response = $this->post('/login', [
            'email' => 'inactive@prowave.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_protected_roles_cannot_be_deleted_and_unassigned_role_can(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $admin->givePermissionTo('roles.delete');

        $this->actingAs($admin);

        $protected = Role::findByName('Super Admin');
        $response = $this->delete(route('admin.roles.destroy', $protected));
        $response->assertSessionHas('toast', ['type' => 'error', 'message' => 'Cannot delete a role that is assigned to users.']);

        $role = Role::create(['name' => 'Temp Role', 'guard_name' => 'web']);
        $response = $this->delete(route('admin.roles.destroy', $role));
        $response->assertSessionHas('toast', ['type' => 'success', 'message' => 'Role deleted successfully.']);
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }
}
