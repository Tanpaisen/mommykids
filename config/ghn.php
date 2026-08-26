<?php

return [
    // Sandbox: https://dev-online-gateway.ghn.vn
    // Production: https://online-gateway.ghn.vn
    'base_url' => env('GHN_BASE_URL', 'https://dev-online-gateway.ghn.vn'),

    'token'   => env('GHN_TOKEN', ''),
    'shop_id' => env('GHN_SHOP_ID', ''),

    // District ID của kho/shop (lấy từ API getDistricts)
    // Ví dụ: 1542 = Quận Cầu Giấy, Hà Nội
    'from_district_id' => env('GHN_FROM_DISTRICT_ID', 1542),
    'from_ward_code'   => env('GHN_FROM_WARD_CODE'),
];