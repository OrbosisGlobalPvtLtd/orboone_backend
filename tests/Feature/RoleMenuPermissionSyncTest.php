<?php

namespace Tests\Feature;

use App\Models\Core\RoleM;
use App\Models\Core\UserM;
use App\Services\AccessControl\PermissionSyncService;
use App\Services\Core\Menu\SidebarMenuResolverS;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RoleMenuPermissionSyncTest extends TestCase
{
    use DatabaseTransactions;

    private UserM $adminUser;
    private UserM $hrUser;
    private RoleM $hrRole;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = RoleM::firstOrCreate(['slug' => 'super_admin'], ['name' => 'Super Admin', 'id' => 1]);
        $this->adminUser = UserM::create([
            'name' => 'Super Admin',
            'email' => 'admin_menu_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $adminRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->adminUser->roles()->sync([$adminRole->id]);

        $this->hrRole = RoleM::firstOrCreate(['slug' => 'hr_admin'], ['name' => 'HR Admin', 'id' => 3]);
        $this->hrUser = UserM::create([
            'name' => 'HR Admin User',
            'email' => 'hr_admin_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'system_role_id' => $this->hrRole->id,
            'is_active' => 1,
            'is_app_access' => 1,
            'is_web_access' => 1,
        ]);
        $this->hrUser->roles()->sync([$this->hrRole->id]);
    }

    public function test_assigning_shift_assignment_menu_syncs_permission_and_shows_in_sidebar(): void
    {
        $shiftMenu = DB::table('menus')->where('route', 'employee.shift-assignment.index')->first();
        $this->assertNotNull($shiftMenu);

        $this->actingAs($this->adminUser);

        $response = $this->put(route('role_menus.update', $this->hrRole->id), [
            'menu_ids' => [$shiftMenu->id],
        ]);

        $response->assertRedirect(route('role_menus.index'));

        // Verify permission was synced
        $this->assertTrue($this->hrUser->fresh()->hasPermission('employee.shift.assign.manage'));

        // Verify sidebar resolver displays the menu
        $resolver = app(SidebarMenuResolverS::class);
        $resolver->clearCache($this->hrUser->id);
        $menus = $resolver->resolveForUser($this->hrUser->fresh());

        $hasShiftMenu = false;
        foreach ($menus as $parentId => $items) {
            foreach ($items as $item) {
                if ($item->route === 'employee.shift-assignment.index') {
                    $hasShiftMenu = true;
                    break 2;
                }
            }
        }

        $this->assertTrue($hasShiftMenu);
    }
}
