<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'sale', 'name' => ['en' => 'For Sale', 'ar' => 'للبيع', 'zh' => '待售']],
            ['slug' => 'mating', 'name' => ['en' => 'Mating', 'ar' => 'للزواج', 'zh' => '配种']],
            ['slug' => 'breeding', 'name' => ['en' => 'Our Breeding', 'ar' => 'إنتاجنا', 'zh' => '我们的繁育']],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
