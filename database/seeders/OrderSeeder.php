<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(5)->get();

        // Nếu chưa có sản phẩm nào, tự tạo 1 sản phẩm mẫu để không bị ngắt seeder
        if ($products->isEmpty()) {
            $products = collect([
                Product::create([
                    'name'  => 'Sữa bột MommyKids Premium 900g',
                    'price' => 350000,
                    'sku'   => 'MK-900G',
                ])
            ]);
        }

        for ($i = 1; $i <= 10; $i++) {
            $product  = $products->random();
            $qty      = rand(1, 3);
            $subtotal = $product->price * $qty;

            $order = Order::create([
                'code'             => 'ORD-' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT), // Thêm mã đơn hàng
                'user_id'          => null,
                'recipient_name'   => fake()->name(),
                'recipient_phone'  => '09' . rand(10000000, 99999999),
                'recipient_email'  => fake()->safeEmail(),
                'province_name'    => 'Hà Nội',
                'district_name'    => 'Cầu Giấy',
                'ward_name'        => 'Dịch Vọng',
                'address_detail'   => fake()->streetAddress(),
                'ghn_province_id'  => 202,
                'ghn_district_id'  => 1542,
                'ghn_ward_code'    => '1A0107',
                'subtotal'         => $subtotal,
                'shipping_fee'     => 35000,
                'discount'         => 0,
                'total'            => $subtotal + 35000,
                'status'           => collect(['pending','confirmed','shipping','delivered'])->random(),
                'payment_method'   => 'cod',
                'payment_status'   => 'unpaid',
            ]);

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'price'        => $product->price,
                'quantity'     => $qty,
                'subtotal'     => $subtotal,
            ]);
        }
    }
}