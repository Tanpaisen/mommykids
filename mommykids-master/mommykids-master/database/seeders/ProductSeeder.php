<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Category::all()->each(function (Category $category) {
            for ($i = 1; $i <= 6; $i++) {
                $hasDiscount = $i % 2 === 0;
                $price = 200000 + ($i * 15000) + ($category->id * 1000);
                $oldPrice = $hasDiscount ? $price + 50000 : null;

                $name = "{$category->name} - mẫu {$i}";

                Product::updateOrCreate(
                    ['slug' => Str::slug($name) . '-' . $category->id . '-' . $i],
                    [
                        'category_id' => $category->id,
                        'name' => $name,
                        'image' => 'https://via.placeholder.com/300?text=' . urlencode($category->name),
                        'price' => $price,
                        'old_price' => $oldPrice,
                        'discount_percent' => $hasDiscount ? 15 : null,
                        'stock' => 100,
                        'is_active' => true,
                    ]
                );
            }
        });
    }
}
