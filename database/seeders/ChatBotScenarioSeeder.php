<?php
namespace Database\Seeders;
use App\Models\ChatBotScenario;
use Illuminate\Database\Seeder;
class ChatBotScenarioSeeder extends Seeder {
    public function run(): void {
        $items=[
            ['name'=>'Chào hỏi','keywords'=>['xin chào','chào','hello','hi'],'response'=>'Xin chào 👋 Mình là trợ lý MommyKids. Bạn cần tư vấn sản phẩm, đơn hàng, voucher hay vận chuyển?','priority'=>10,'is_active'=>true,'handoff_to_staff'=>false],
            ['name'=>'Vận chuyển GHN','keywords'=>['phí ship','ship','vận chuyển','giao hàng','GHN'],'response'=>'Phí vận chuyển được tính theo địa chỉ nhận hàng và dữ liệu GHN tại bước thanh toán.','priority'=>20,'is_active'=>true,'handoff_to_staff'=>false],
            ['name'=>'Voucher và khuyến mãi','keywords'=>['voucher','mã giảm','khuyến mãi','giảm giá','ưu đãi'],'response'=>'Bạn có thể chọn voucher đơn hàng và voucher vận chuyển tại bước thanh toán. Hệ thống sẽ kiểm tra điều kiện trước khi áp dụng.','priority'=>30,'is_active'=>true,'handoff_to_staff'=>false],
            ['name'=>'Yêu cầu gặp nhân viên','keywords'=>['gặp nhân viên','tư vấn viên','người thật','hỗ trợ viên'],'response'=>'MommyKids đã nhận yêu cầu. Mình đang kết nối bạn với chuyên viên.','priority'=>1,'is_active'=>true,'handoff_to_staff'=>true],
        ];
        foreach($items as $item){ ChatBotScenario::updateOrCreate(['name'=>$item['name']],$item); }
    }
}
