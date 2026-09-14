<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            [
                'name' => 'Bầu 3 tháng đầu',
                'age_from' => 0,
                'age_to' => 3,
                'description' => 'Giai đoạn đầu thai kỳ, mẹ cần chú ý dinh dưỡng, nghỉ ngơi và theo dõi sức khỏe thường xuyên.',
                'icon' => '🤰',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Bầu 3 tháng giữa',
                'age_from' => 4,
                'age_to' => 6,
                'description' => 'Thai nhi phát triển nhanh, mẹ cần bổ sung dưỡng chất và duy trì vận động nhẹ phù hợp.',
                'icon' => '🤱',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Bầu 3 tháng cuối',
                'age_from' => 7,
                'age_to' => 9,
                'description' => 'Giai đoạn chuẩn bị sinh, mẹ cần theo dõi sức khỏe và chuẩn bị các vật dụng cần thiết cho bé.',
                'icon' => '🧸',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Sơ sinh 0-3 tháng',
                'age_from' => 0,
                'age_to' => 3,
                'description' => 'Giai đoạn bé sơ sinh và bắt đầu làm quen với môi trường bên ngoài.',
                'icon' => '👶',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Bé 3-6 tháng',
                'age_from' => 3,
                'age_to' => 6,
                'description' => 'Bé phát triển khả năng vận động, giao tiếp và tương tác với người thân nhiều hơn.',
                'icon' => '🍼',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Bé 6-12 tháng',
                'age_from' => 6,
                'age_to' => 12,
                'description' => 'Bé bắt đầu ăn dặm, tập ngồi, tập bò và khám phá nhiều thực phẩm cũng như đồ vật xung quanh.',
                'icon' => '🥣',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Bé 12-24 tháng',
                'age_from' => 12,
                'age_to' => 24,
                'description' => 'Bé tập đi, tập nói và hình thành những kỹ năng tự lập cơ bản.',
                'icon' => '🧒',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Bé 2-3 tuổi',
                'age_from' => 24,
                'age_to' => 36,
                'description' => 'Bé tăng khả năng giao tiếp, ghi nhớ, học hỏi và phát triển vận động rõ rệt.',
                'icon' => '🧩',
                'sort_order' => 8,
                'is_active' => false,
            ],
            [
                'name' => 'Bé 3-6 tuổi',
                'age_from' => 36,
                'age_to' => 72,
                'description' => 'Giai đoạn phát triển mạnh về tư duy, cảm xúc, ngôn ngữ và kỹ năng xã hội.',
                'icon' => '🎨',
                'sort_order' => 9,
                'is_active' => true,
            ],
        ];

        foreach ($stages as $stage) {
            Stage::updateOrCreate(
                ['name' => $stage['name']],
                $stage
            );
        }
    }
}