<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Hardware',
                'description' => 'Issues related to computer hardware',
                'active' => true,
            ],
            [
                'name' => 'Software',
                'description' => 'Issues related to software applications',
                'active' => true,
            ],
            [
                'name' => 'Network',
                'description' => 'Network connectivity issues',
                'active' => true,
            ],
            [
                'name' => 'Accounts',
                'description' => 'User account related issues',
                'active' => true,
            ],
            [
                'name' => 'Other',
                'description' => 'Miscellaneous issues',
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}