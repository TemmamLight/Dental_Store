<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;




use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a super admin user
        $user = User::factory()->create([
            'name' => 'abod',
            'email' => 'abod@a.com',
            'password' => bcrypt('password'),
        ]);

        // Seed other data
        Brand::factory()->count(3)->create();
        Customer::factory()->count(5)->create();
        Category::factory()->count(5)->create();
        Product::factory()->count(10)->create();

        // Define the basic permissions based on your policies
        $permissions = ['view-any', 'view', 'create', 'update', 'delete', 'restore', 'force-delete'];
        $models = ['User', 'Product', 'Brand', 'Customer', 'Category', 'Order', 'Section', 'Role', 'Permission'];

        // Create permissions for each model
        foreach ($models as $model) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => "$permission $model"]);
            }
        }

        // Create roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign all permissions to super admin
        $superAdminRole->syncPermissions(Permission::all());
        
        // Assign the first user the super admin role
        $user->assignRole($superAdminRole);
    }
}