<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\LoginLog;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthAccessControlFullTest extends TestCase
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

    public function test_seed_state_roles_and_permissions_counts(): void
    {
        $this->assertSame(11, Role::count());
        $this->assertSame(147, Permission::count());

        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->assertNotNull($admin);
        $this->assertSame(UserStatus::Active->value, $admin->status->value);
        $this->assertTrue($admin->hasRole('Super Admin'));
    }

    public function test_admin_login_and_dashboard_access(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@prowave.com',
            'password' => 'password',
        ]);

        $response->assertRedirectToRoute('dashboard');

        $this->assertAuthenticated();

        $this->get(route('dashboard'))->assertOk();

        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->assertNotNull($admin->last_login_at);
        $this->assertSame(0, $admin->login_attempts);
    }

    public function test_login_log_recorded_on_success(): void
    {
        $this->post('/login', [
            'email' => 'admin@prowave.com',
            'password' => 'password',
        ]);

        $admin = User::where('email', 'admin@prowave.com')->first();

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $admin->id,
            'status' => 'success',
        ]);
    }

    public function test_logout_records_logout_at(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();

        $this->post('/login', [
            'email' => 'admin@prowave.com',
            'password' => 'password',
        ]);

        $log = LoginLog::where('user_id', $admin->id)->where('status', 'success')->first();

        $this->post(route('logout'));

        $this->assertNotNull($log->fresh()->logout_at);
    }

    public function test_suspended_user_cannot_login(): void
    {
        auth()->logout();
        session()->flush();

        $suspended = User::factory()->create([
            'email' => 'suspended@prowave.com',
            'status' => UserStatus::Suspended->value,
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);
        $suspended->assignRole('Developer');

        $response = $this->post('/login', [
            'email' => 'suspended@prowave.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        auth()->logout();
        session()->flush();

        $inactive = User::factory()->create([
            'email' => 'inactive@prowave.com',
            'status' => UserStatus::Inactive->value,
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);
        $inactive->assignRole('Developer');

        $response = $this->post('/login', [
            'email' => 'inactive@prowave.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_admin_can_access_users_pages(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.users.create'))->assertOk();
        $this->get(route('admin.users.data'))->assertOk();
        $this->get(route('admin.users.import'))->assertOk();
        $this->get(route('admin.roles.index'))->assertOk();
        $this->get(route('admin.roles.create'))->assertOk();
        $this->get(route('admin.permissions.index'))->assertOk();
        $this->get(route('admin.departments.index'))->assertOk();
    }

    public function test_admin_can_access_user_edit_and_roles_edit(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $user = User::factory()->create([
            'email' => 'edit-target@prowave.com',
            'status' => UserStatus::Active->value,
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Developer');

        $this->get(route('admin.users.edit', $user))->assertOk();
        $this->get(route('admin.users.show', $user))->assertOk();
        $this->get(route('admin.users.login-logs', $user))->assertOk();

        $role = Role::where('name', 'Developer')->first();
        $this->get(route('admin.roles.edit', $role))->assertOk();
    }

    public function test_permissions_filter_by_role_and_search(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $this->get(route('admin.permissions.index', ['role' => 'Developer']))->assertOk();
        $this->get(route('admin.permissions.index', ['search' => 'users.view']))->assertOk();
        $this->get(route('admin.permissions.index', ['search' => 'users.view', 'role' => 'Developer']))->assertOk();
    }

    public function test_profile_pages_and_security_tabs(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $tabs = ['overview', 'personal', 'work', 'security', 'activity', 'login-history', 'permissions', 'settings'];

        foreach ($tabs as $tab) {
            $this->get(route('profile.show', ['tab' => $tab]))->assertOk();
        }

        $this->get(route('profile.edit'))->assertOk();
        $this->get(route('profile.edit', ['tab' => 'security']))->assertOk();
        $this->get(route('profile.edit', ['tab' => 'preferences']))->assertOk();
    }

    public function test_password_change_end_to_end(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $response = $this->patch(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
            'tab' => 'security',
        ]);

        $response->assertSessionHas('toast', ['type' => 'success', 'message' => 'Password updated successfully.']);

        $this->assertTrue(Hash::check('NewPassword123', $admin->fresh()->password));

        $this->assertDatabaseHas('activity_log', [
            'description' => 'Password changed',
            'log_name' => 'security',
        ]);
    }

    public function test_password_change_rejects_wrong_current_password(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $response = $this->patch(route('profile.password'), [
            'current_password' => 'wrong-password',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_role_delete_protection_for_super_admin_and_administrator(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $superAdmin = Role::where('name', 'Super Admin')->first();
        $this->delete(route('admin.roles.destroy', $superAdmin))
            ->assertSessionHas('toast', ['type' => 'error', 'message' => 'Cannot delete a role that is assigned to users.']);

        $administrator = Role::where('name', 'Administrator')->first();
        $this->delete(route('admin.roles.destroy', $administrator))
            ->assertSessionHas('toast', ['type' => 'error', 'message' => 'Cannot delete a role that is assigned to users.']);

        $this->assertDatabaseHas('roles', ['name' => 'Super Admin']);
        $this->assertDatabaseHas('roles', ['name' => 'Administrator']);
    }

    public function test_unassigned_role_can_be_deleted(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $role = Role::create(['name' => 'Temp Role', 'guard_name' => 'web']);

        $this->delete(route('admin.roles.destroy', $role))
            ->assertSessionHas('toast', ['type' => 'success', 'message' => 'Role deleted successfully.']);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_assigned_role_cannot_be_deleted(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $developer = Role::where('name', 'Developer')->first();

        $user = User::factory()->create([
            'email' => 'has-role@prowave.com',
            'status' => UserStatus::Active->value,
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Developer');

        $this->delete(route('admin.roles.destroy', $developer))
            ->assertSessionHas('toast', ['type' => 'error', 'message' => 'Cannot delete a role that is assigned to users.']);

        $this->assertDatabaseHas('roles', ['name' => 'Developer']);
    }

    public function test_non_admin_cannot_access_admin_users(): void
    {
        auth()->logout();
        session()->flush();

        $user = User::factory()->create([
            'email' => 'basic@prowave.com',
            'status' => UserStatus::Active->value,
            'password' => Hash::make('password'),
        ]);
        $user->assignRole('Developer');

        $this->actingAs($user);

        $this->get(route('admin.users.index'))->assertForbidden();
        $this->get(route('admin.users.create'))->assertForbidden();
    }

    public function test_user_can_update_their_own_profile(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $this->patch(route('profile.update'), [
            'name' => 'Prowave Administrator Updated',
            'email' => 'admin@prowave.com',
            'tab' => 'personal',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Prowave Administrator Updated',
        ]);
    }

    public function test_departments_renders_for_admin(): void
    {
        $admin = User::where('email', 'admin@prowave.com')->first();
        $this->actingAs($admin);

        $department = Department::where('code', 'IT')->first();
        $this->assertNotNull($department);

        $this->get(route('admin.departments.index'))->assertOk();
        $this->get(route('admin.departments.create'))->assertOk();
        $this->get(route('admin.departments.edit', $department))->assertOk();
    }
}
