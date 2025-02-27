<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;




use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
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

        // Automatically sync permissions as defined in the package configuration
        Artisan::call('permissions:sync');

        // Create roles if they do not exist
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Assign the first user the super admin role
        $user->assignRole($superAdminRole);

    }
}