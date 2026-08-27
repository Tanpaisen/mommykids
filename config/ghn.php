<?php

return [
    'base_url' => env('GHN_BASE_URL', 'https://dev-online-gateway.ghn.vn'),

    'token' => env('GHN_TOKEN'),

    'shop_id' => (int) env('GHN_SHOP_ID'),

    'from_district_id' => (int) env('GHN_FROM_DISTRICT_ID'),

    'from_ward_code' => env('GHN_FROM_WARD_CODE'),
];