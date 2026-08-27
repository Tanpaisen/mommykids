<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Organic',
                'slug' => 'organic',
                'type' => 'attribute',
            ],
            [
                'name' => 'Da nhạy cảm',
                'slug' => 'da-nhay-cam',
                'type' => 'attribute',
            ],
            [
                'name' => 'Không đường',
                'slug' => 'khong-duong',
                'type' => 'attribute',
            ],
            [
                'name' => 'Không chất bảo quản',
                'slug' => 'khong-chat-bao-quan',
                'type' => 'attribute',
            ],
            [
                'name' => 'Dành cho sơ sinh',
                'slug' => 'danh-cho-so-sinh',
                'type' => 'stage',
            ],
            [
                'name' => 'Dành cho bé ăn dặm',
                'slug' => 'danh-cho-be-an-dam',
                'type' => 'stage',
            ],
            [
                'name' => 'Aptamil',
                'slug' => 'aptamil',
                'type' => 'brand',
            ],
            [
                'name' => 'Meiji',
                'slug' => 'meiji',
                'type' => 'brand',
            ],
            [
                'name' => 'Pigeon',
                'slug' => 'pigeon',
                'type' => 'brand',
            ],
            [
                'name' => 'Merries',
                'slug' => 'merries',
                'type' => 'brand',
            ],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => $tag['slug']],
                $tag
            );
        }
    }
}