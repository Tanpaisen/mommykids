<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Sữa cho bé',           'icon' => '🍼', 'slug' => 'sua-cho-be'],
            ['name' => 'Bỉm tã & vệ sinh',      'icon' => '🧷', 'slug' => 'bim-ta-ve-sinh'],
            ['name' => 'Bình sữa & phụ kiện',   'icon' => '🍶', 'slug' => 'binh-sua-phu-kien'],
            ['name' => 'Ăn dặm, dinh dưỡng',    'icon' => '🥣', 'slug' => 'an-dam-dinh-duong'],
            ['name' => 'Vitamin & sức khỏe',    'icon' => '💊', 'slug' => 'vitamin-suc-khoe'],
            ['name' => 'Đồ dùng mẹ & bé',       'icon' => '🧴', 'slug' => 'do-dung-me-be'],
            ['name' => 'Đồ sơ sinh',            'icon' => '👶', 'slug' => 'do-so-sinh'],
            ['name' => 'Thời trang & phụ kiện', 'icon' => '👗', 'slug' => 'thoi-trang-phu-kien'],
            ['name' => 'Xe cho bé',             'icon' => '🚼', 'slug' => 'xe-cho-be'],
            ['name' => 'Đồ chơi, học tập',      'icon' => '🧸', 'slug' => 'do-choi-hoc-tap'],
            ['name' => 'Mẹ bầu & sau sinh',     'icon' => '🤰', 'slug' => 'me-bau-sau-sinh'],
            ['name' => 'Chăm sóc gia đình',     'icon' => '🏡', 'slug' => 'cham-soc-gia-dinh'],
        ];

        foreach ($categories as $i => $cat) {
            Category::updateOrCreate(
                ['slug' => $cat['slug']],
                $cat + ['sort_order' => $i, 'is_active' => true]
            );
        }
    }
}
