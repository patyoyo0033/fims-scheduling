<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // สร้าง Permissions
        Permission::create(['name' => 'manage master data']);
        Permission::create(['name' => 'create schedule']);
        Permission::create(['name' => 'approve schedule']);
        Permission::create(['name' => 'view all schedules']);

        // สร้าง Roles และผูก Permissions
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $head = Role::create(['name' => 'head_of_course']); // หัวหน้าวิชา
        $head->givePermissionTo(['create schedule', 'approve schedule']);

        $staff = Role::create(['name' => 'staff']);
        $staff->givePermissionTo(['manage master data', 'view all schedules']);

        $teacher = Role::create(['name' => 'teacher']);
        
        // สร้าง User ทดสอบ
        $user = \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@mu.ac.th',
        ]);
        $user->assignRole($admin);
    }
}
