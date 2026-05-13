<?php

namespace Tests\Unit;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\SidebarService;

class SuperAdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_has_sidebar_permissions()
    {
        // Find the user with ID 13 (the Super Admin)
        $user = User::find(13);
        
        $this->assertNotNull($user, 'User with ID 13 not found');
        
        // Test that the user has key permissions
        $this->assertTrue($user->hasPermissionTo('view dashboard'), 'User should have view dashboard permission');
        $this->assertTrue($user->hasPermissionTo('view settings'), 'User should have view settings permission');
        $this->assertTrue($user->hasPermissionTo('view financial'), 'User should have view financial permission');
        
        // Test the sidebar service directly
        $sidebarService = app(SidebarService::class);
        $menuItems = $sidebarService->getPermittedMenu($user);
        
        $this->assertNotEmpty($menuItems, 'Sidebar menu should not be empty for Super Admin');
        $this->assertGreaterThan(0, count($menuItems), 'Super Admin should have menu items in sidebar');
        
        // Check for key menu groups
        $menuKeys = array_column($menuItems, 'key');
        $expectedGroups = ['dashboard_group', 'finance_group', 'operations_group', 'settings_group'];
        
        foreach ($expectedGroups as $expectedGroup) {
            $this->assertContains($expectedGroup, $menuKeys, "Sidebar should contain {$expectedGroup} group");
        }
        
        echo "\nSidebar menu items count: " . count($menuItems) . "\n";
    }
}