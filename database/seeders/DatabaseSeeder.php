<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'abod ',
            'email' => 'abod@a.com',
            'password'=>'password',
        ]);
        Brand::factory()->count(3)->create();
        Customer::factory()->count(5)->create();
        Category::factory()->count(5)->create();
        $products = Product::factory()->count(10)->create();

    }
}