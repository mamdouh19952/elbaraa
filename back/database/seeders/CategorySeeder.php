<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'sale', 'name_en' => 'For Sale', 'name_ar' => 'للبيع'],
            ['slug' => 'mating', 'name_en' => 'Mating', 'name_ar' => 'للزواج'],
            ['slug' => 'breeding', 'name_en' => 'Our Breeding', 'name_ar' => 'إنتاجنا'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
