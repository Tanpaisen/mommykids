<?php



namespace App\Services;



use Illuminate\Http\Client\Response;



use Illuminate\Support\Facades\Cache;



use Illuminate\Support\Facades\Http;



use Illuminate\Support\Facades\Log;



use RuntimeException;



use InvalidArgumentException;



class GHNService



{



    private string $baseUrl;



    private string $token;



    private int $shopId;



    /**



     * Cache TTL



     */



    private const MASTER_DATA_TTL = 86400;          // 24 giờ



    private const SERVICE_OK_TTL = 86400;           // 24 giờ



    private const SERVICE_EMPTY_TTL = 3600;         // 1 giờ - tránh giữ tuyến lỗi quá lâu



    public function __construct()



    {



        $this->baseUrl = rtrim((string) config('ghn.base_url'), '/');



        $this->token   = (string) (config('ghn.token') ?? '');



        $this->shopId  = (int) config('ghn.shop_id');



    }



    // ─────────────────────────────────────────────────────────────────────────



    // CẤU HÌNH



    // ─────────────────────────────────────────────────────────────────────────



    private function ensureShippingConfigured(): void



    {



        if ($this->baseUrl === '') {



            throw new RuntimeException('Thiếu cấu hình GHN_BASE_URL.');



        }



        if ($this->token === '') {



            throw new RuntimeException('Thiếu cấu hình GHN_TOKEN.');



        }



        if ($this->shopId <= 0) {



            throw new RuntimeException('Thiếu hoặc sai GHN_SHOP_ID.');



        }



        if ((int) config('ghn.from_district_id') <= 0) {



            throw new RuntimeException('Thiếu hoặc sai GHN_FROM_DISTRICT_ID.');



        }



        if ((string) config('ghn.from_ward_code') === '') {



            throw new RuntimeException('Thiếu GHN_FROM_WARD_CODE.');



        }



    }



    private function fromDistrictId(): int



    {



        return (int) config('ghn.from_district_id');



    }



    private function fromWardCode(): string



    {



        return (string) config('ghn.from_ward_code');



    }



    // ─────────────────────────────────────────────────────────────────────────



    // ĐỊA CHỈ GHN - MASTER DATA



    // ─────────────────────────────────────────────────────────────────────────



    /**



     * Danh sách tỉnh/thành GHN.



     */



    public function getProvinces(): array



    {



        return Cache::remember(



            'ghn:master:provinces',



            self::MASTER_DATA_TTL,



            fn () => $this->get('/shiip/public-api/master-data/province')



        );



    }



    /**



     * Danh sách quận/huyện thô theo tỉnh.



     */



    public function getDistricts(int $provinceId): array



    {



        if ($provinceId <= 0) {



            return [];



        }



        return Cache::remember(



            "ghn:master:districts:{$provinceId}",



            self::MASTER_DATA_TTL,



            fn () => $this->post('/shiip/public-api/master-data/district', [



                'province_id' => $provinceId,



            ])



        );



    }



    /**



     * Danh sách phường/xã thô theo quận/huyện.



     */



    public function getWards(int $districtId): array



    {



        if ($districtId <= 0) {



            return [];



        }



        return Cache::remember(



            "ghn:master:wards:{$districtId}",



            self::MASTER_DATA_TTL,



            fn () => $this->post('/shiip/public-api/master-data/ward', [



                'district_id' => $districtId,



            ])



        );



    }



    /**



     * Chỉ giữ ward có khả năng giao:



     * SupportType = 2 (chỉ giao) hoặc 3 (lấy + giao).



     */



    public function getDeliverableWards(
        int $districtId,
        bool $forceRefresh = false
    ): array {
        if ($districtId <= 0) {
            return [];
        }

        $key = "ghn:deliverable:wards:{$districtId}";
        $stale = Cache::get($key);

        /*
         * Request web bình thường chỉ đọc cache đã warm.
         * Cache này được giữ lâu dài để khách không phải chờ GHN
         * chỉ vì TTL vừa hết hạn.
         */
        if (!$forceRefresh && is_array($stale)) {
            return $stale;
        }

        try {
            $wards = $this->getWards($districtId);

            $result = collect($wards)
                ->filter(function ($ward) {
                    if (!is_array($ward)) {
                        return false;
                    }

                    $wardCode = trim((string) ($ward['WardCode'] ?? ''));
                    $wardName = trim((string) ($ward['WardName'] ?? ''));

                    if ($wardCode === '' || $wardName === '') {
                        return false;
                    }

                    /*
                     * 0 = không hỗ trợ
                     * 1 = chỉ lấy hàng
                     * 2 = chỉ giao
                     * 3 = lấy + giao
                     */
                    $supportType = (int) ($ward['SupportType'] ?? 0);

                    if (!in_array($supportType, [2, 3], true)) {
                        return false;
                    }

                    if ((int) ($ward['IsEnable'] ?? 1) !== 1) {
                        return false;
                    }

                    if ((int) ($ward['Status'] ?? 1) !== 1) {
                        return false;
                    }

                    $lockType = data_get($ward, 'Config.To.LockType');

                    if (
                        $lockType !== null &&
                        strtolower((string) $lockType) !== 'unlocked'
                    ) {
                        return false;
                    }

                    return true;
                })
                ->map(function ($ward) {
                    return [
                        'WardCode' => (string) $ward['WardCode'],
                        'WardName' => (string) $ward['WardName'],
                        'DistrictID' => (int) ($ward['DistrictID'] ?? 0),
                        'SupportType' => (int) ($ward['SupportType'] ?? 0),
                    ];
                })
                ->values()
                ->all();

            /*
             * Không TTL cho danh sách ward đã lọc.
             * Scheduler/force warm sẽ ghi đè bằng dữ liệu mới.
             */
            Cache::forever($key, $result);

            return $result;
        } catch (\Throwable $e) {
            /*
             * Refresh lỗi mạng/API thì giữ dữ liệu cũ,
             * không làm checkout của khách mất ward.
             */
            if (is_array($stale)) {
                Log::warning('GHN ward refresh failed, serving stale cache', [
                    'district_id' => $districtId,
                    'error' => $e->getMessage(),
                ]);

                return $stale;
            }

            throw $e;
        }
    }



    // ─────────────────────────────────────────────────────────────────────────



    // SHOP



    // ─────────────────────────────────────────────────────────────────────────



    /**



     * Lấy danh sách shop thuộc token hiện tại.



     */



    public function getShops(int $offset = 0, int $limit = 20): array



    {



        return $this->get('/shiip/public-api/v2/shop/all', [



            'offset' => max(0, $offset),



            'limit'  => max(1, min(100, $limit)),



        ]);



    }



    // ─────────────────────────────────────────────────────────────────────────



    // TUYẾN GIAO HÀNG + REDIS CACHE



    // ─────────────────────────────────────────────────────────────────────────



    /**



     * Gọi trực tiếp available-services.



     *



     * GHN có thể trả code=200, data=null khi tuyến không có dịch vụ.



     * parseResponse() sẽ biến data=null thành [].



     */



    public function getAvailableServices(int $toDistrictId): array



    {



        $this->ensureShippingConfigured();



        if ($toDistrictId <= 0) {



            return [];



        }



        return $this->post(



            '/shiip/public-api/v2/shipping-order/available-services',



            [



                'shop_id'       => $this->shopId,



                'from_district' => $this->fromDistrictId(),



                'to_district'   => $toDistrictId,



            ]



        );



    }



    /**



     * Cache theo đúng tuyến:



     * shop + from_district + to_district.



     *



     * Tuyến có service: cache 24h.



     * Tuyến không có service: cache 1h để tránh giữ false-negative quá lâu.



     */



    public function getAvailableServicesCached(
        int $toDistrictId,
        bool $forceRefresh = false
    ): array {
        $this->ensureShippingConfigured();

        $key = $this->serviceCacheKey($toDistrictId);
        $stale = Cache::get($key);

        if (!$forceRefresh && is_array($stale)) {
            return $stale;
        }

        try {
            /*
             * Force refresh chỉ bỏ qua cache đọc.
             * Không xóa cache cũ trước khi request mới thành công.
             */
            $services = $this->getAvailableServices($toDistrictId);

            Cache::put(
                $key,
                $services,
                empty($services)
                    ? self::SERVICE_EMPTY_TTL
                    : self::SERVICE_OK_TTL
            );

            return $services;
        } catch (\Throwable $e) {
            if (is_array($stale)) {
                Log::warning('GHN service refresh failed, serving stale cache', [
                    'to_district_id' => $toDistrictId,
                    'error' => $e->getMessage(),
                ]);

                return $stale;
            }

            throw $e;
        }
    }



    /**



     * Danh sách huyện thật sự có thể giao từ shop hiện tại.



     *



     * Điều kiện:



     * - district có SupportType 2 hoặc 3 (nếu GHN trả field này);



     * - available-services của tuyến không rỗng.



     */



    public function getDeliverableDistricts(
        int $provinceId,
        bool $forceRefresh = false
    ): array {
        if ($provinceId <= 0) {
            return [];
        }

        $key = $this->deliverableDistrictCacheKey($provinceId);
        $stale = Cache::get($key);

        /*
         * RẤT QUAN TRỌNG:
         * request của khách KHÔNG tự quét toàn bộ huyện của tỉnh.
         * Nếu chưa có cache thì trả [] ngay và chờ scheduler/warm.
         */
        if (!$forceRefresh) {
            if (is_array($stale)) {
                return $stale;
            }

            Log::warning('GHN deliverable districts cache miss', [
                'province_id' => $provinceId,
            ]);

            return [];
        }

        try {
            $districts = $this->getDistricts($provinceId);
        } catch (\Throwable $e) {
            if (is_array($stale)) {
                Log::warning('GHN district refresh failed, serving stale cache', [
                    'province_id' => $provinceId,
                    'error' => $e->getMessage(),
                ]);

                return $stale;
            }

            throw $e;
        }

        $result = [];
        $hadErrors = false;

        foreach ($districts as $district) {
            $districtId = (int) ($district['DistrictID'] ?? 0);

            if ($districtId <= 0) {
                continue;
            }

            if (array_key_exists('SupportType', $district)) {
                $supportType = (int) $district['SupportType'];

                if (!in_array($supportType, [2, 3], true)) {
                    continue;
                }
            }

            try {
                $services = $this->getAvailableServicesCached(
                    $districtId,
                    true
                );

                if (empty($services)) {
                    continue;
                }

                $wards = $this->getDeliverableWards(
                    $districtId,
                    true
                );

                if (empty($wards)) {
                    continue;
                }

                $district['HasGHNService'] = true;
                $district['DeliverableWardCount'] = count($wards);
                $result[] = $district;
            } catch (\Throwable $e) {
                $hadErrors = true;

                Log::warning('GHN skip district while refreshing deliverable list', [
                    'province_id' => $provinceId,
                    'district_id' => $districtId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        /*
         * Nếu refresh có lỗi mà đã có cache cũ,
         * giữ nguyên cache cũ để tránh false-negative.
         */
        if ($hadErrors && is_array($stale)) {
            Log::warning('GHN province refresh incomplete, keeping stale districts', [
                'province_id' => $provinceId,
                'stale_count' => count($stale),
            ]);

            return $stale;
        }

        Cache::forever($key, $result);

        return $result;
    }



    /**



     * Danh sách tỉnh có ít nhất 1 huyện giao được.



     *



     * Request web chỉ đọc cache đã warm.

     * Scheduler/Artisan chịu trách nhiệm refresh cache GHN.



     */



    public function getDeliverableProvinces(bool $forceRefresh = false): array
    {
        $key = $this->deliverableProvinceCacheKey();
        $stale = Cache::get($key);

        /*
         * Request web bình thường CHỈ đọc Redis.
         * Tuyệt đối không để khách đầu tiên phải quét 65 tỉnh.
         */
        if (!$forceRefresh) {
            if (is_array($stale)) {
                return $stale;
            }

            Log::warning('GHN deliverable provinces cache miss');

            return [];
        }

        try {
            $provinces = $this->getProvinces();
        } catch (\Throwable $e) {
            if (is_array($stale)) {
                Log::warning('GHN province refresh failed, serving stale cache', [
                    'error' => $e->getMessage(),
                ]);

                return $stale;
            }

            throw $e;
        }

        $result = [];
        $hadErrors = false;

        foreach ($provinces as $province) {
            $provinceId = (int) ($province['ProvinceID'] ?? 0);

            if ($provinceId <= 0) {
                continue;
            }

            try {
                $districts = $this->getDeliverableDistricts(
                    $provinceId,
                    true
                );
            } catch (\Throwable $e) {
                $hadErrors = true;

                Log::warning('GHN skip province while refreshing deliverable list', [
                    'province_id' => $provinceId,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            if (!empty($districts)) {
                $result[] = $province;
            }
        }

        if ($hadErrors && is_array($stale)) {
            Log::warning('GHN province refresh incomplete, keeping stale province list', [
                'stale_count' => count($stale),
            ]);

            return $stale;
        }

        Cache::forever($key, $result);

        return $result;
    }



    /**



     * Pre-warm toàn bộ tuyến GHN vào cache.



     *



     * Dùng từ Artisan command, ví dụ cache:warm:



     *



     *   $stats = app(GHNService::class)->warmDeliverableRoutes();



     *



     * Sau khi chạy:



     * - tỉnh hỗ trợ được cache;



     * - huyện hỗ trợ theo từng tỉnh được cache;



     * - available-services theo từng tuyến được cache;



     * - ward giao được của các huyện hợp lệ được cache.



     *



     * $delayMs giúp tránh gọi GHN dồn dập.



     */



    public function warmDeliverableRoutes(
        bool $forceRefresh = false,
        int $delayMs = 150
    ): array {
        $this->ensureShippingConfigured();

        $stats = [
            'provinces_scanned' => 0,
            'provinces_deliverable' => 0,
            'districts_scanned' => 0,
            'districts_deliverable' => 0,
            'districts_without_wards' => 0,
            'wards_cached' => 0,
            'routes_without_service' => 0,
            'errors' => 0,
            'stale_district_lists_preserved' => 0,
            'stale_province_list_preserved' => 0,
        ];

        $provinceCacheKey = $this->deliverableProvinceCacheKey();
        $staleProvinceList = Cache::get($provinceCacheKey);

        try {
            $provinces = $this->getProvinces();
        } catch (\Throwable $e) {
            $stats['errors']++;

            Log::warning('GHN warm provinces failed', [
                'error' => $e->getMessage(),
            ]);

            if (is_array($staleProvinceList)) {
                $stats['stale_province_list_preserved'] = 1;
                $stats['provinces_deliverable'] = count($staleProvinceList);
                return $stats;
            }

            throw $e;
        }

        $deliverableProvinces = [];
        $globalHadErrors = false;

        foreach ($provinces as $province) {
            $provinceId = (int) ($province['ProvinceID'] ?? 0);

            if ($provinceId <= 0) {
                continue;
            }

            $stats['provinces_scanned']++;

            $districtCacheKey = $this->deliverableDistrictCacheKey($provinceId);
            $staleDistricts = Cache::get($districtCacheKey);

            try {
                $districts = $this->getDistricts($provinceId);
            } catch (\Throwable $e) {
                $stats['errors']++;
                $globalHadErrors = true;

                Log::warning('GHN warm province districts failed', [
                    'province_id' => $provinceId,
                    'error' => $e->getMessage(),
                ]);

                if (is_array($staleDistricts)) {
                    $stats['stale_district_lists_preserved']++;

                    if (!empty($staleDistricts)) {
                        $deliverableProvinces[] = $province;
                        $stats['provinces_deliverable']++;
                    }
                }

                continue;
            }

            $deliverableDistricts = [];
            $provinceHadErrors = false;

            foreach ($districts as $district) {
                $districtId = (int) ($district['DistrictID'] ?? 0);

                if ($districtId <= 0) {
                    continue;
                }

                $stats['districts_scanned']++;

                if (array_key_exists('SupportType', $district)) {
                    $supportType = (int) $district['SupportType'];

                    if (!in_array($supportType, [2, 3], true)) {
                        continue;
                    }
                }

                try {
                    $services = $this->getAvailableServicesCached(
                        $districtId,
                        $forceRefresh
                    );

                    if (empty($services)) {
                        $stats['routes_without_service']++;
                    } else {
                        $wards = $this->getDeliverableWards(
                            $districtId,
                            $forceRefresh
                        );

                        if (empty($wards)) {
                            $stats['districts_without_wards']++;
                        } else {
                            $district['HasGHNService'] = true;
                            $district['DeliverableWardCount'] = count($wards);
                            $deliverableDistricts[] = $district;
                            $stats['districts_deliverable']++;
                            $stats['wards_cached'] += count($wards);
                        }
                    }
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    $provinceHadErrors = true;
                    $globalHadErrors = true;

                    Log::warning('GHN warm route failed', [
                        'province_id' => $provinceId,
                        'district_id' => $districtId,
                        'error' => $e->getMessage(),
                    ]);
                }

                if ($delayMs > 0) {
                    usleep($delayMs * 1000);
                }
            }

            /*
             * Một vài route lỗi trong tỉnh -> giữ nguyên danh sách huyện cũ
             * nếu đã có, không ghi danh sách thiếu lên Redis.
             */
            if ($provinceHadErrors && is_array($staleDistricts)) {
                $effectiveDistricts = $staleDistricts;
                $stats['stale_district_lists_preserved']++;
            } else {
                Cache::forever($districtCacheKey, $deliverableDistricts);
                $effectiveDistricts = $deliverableDistricts;
            }

            if (!empty($effectiveDistricts)) {
                $deliverableProvinces[] = $province;
                $stats['provinces_deliverable']++;
            }
        }

        /*
         * Nếu cả vòng warm có lỗi và đã có danh sách tỉnh cũ,
         * giữ nguyên danh sách cũ. Nếu chưa từng có cache thì dùng
         * dữ liệu hợp lệ thu được ở lần warm đầu tiên.
         */
        if ($globalHadErrors && is_array($staleProvinceList)) {
            $stats['stale_province_list_preserved'] = 1;
            $stats['provinces_deliverable'] = count($staleProvinceList);
        } else {
            Cache::forever(
                $provinceCacheKey,
                $deliverableProvinces
            );
        }

        Log::info('GHN route cache warmed', $stats);

        return $stats;
    }



    /**



     * Xóa cache tuyến theo shop/from hiện tại.



     *



     * Lưu ý: Cache facade không hỗ trợ wildcard forget theo driver,



     * nên method này xóa các key tổng và key theo danh sách master hiện có.



     */



    public function clearDeliverableRouteCache(): void



    {



        Cache::forget($this->deliverableProvinceCacheKey());



        foreach ($this->getProvinces() as $province) {



            $provinceId = (int) ($province['ProvinceID'] ?? 0);



            if ($provinceId <= 0) {



                continue;



            }



            Cache::forget($this->deliverableDistrictCacheKey($provinceId));



            foreach ($this->getDistricts($provinceId) as $district) {



                $districtId = (int) ($district['DistrictID'] ?? 0);



                if ($districtId <= 0) {



                    continue;



                }



                Cache::forget($this->serviceCacheKey($districtId));



                Cache::forget("ghn:deliverable:wards:{$districtId}");



            }



        }



    }



    private function serviceCacheKey(int $toDistrictId): string



    {



        return sprintf(



            'ghn:services:%d:%d:%d',



            $this->shopId,



            $this->fromDistrictId(),



            $toDistrictId



        );



    }



    private function deliverableDistrictCacheKey(int $provinceId): string



    {



        return sprintf(



            'ghn:deliverable:districts:%d:%d:%d',



            $this->shopId,



            $this->fromDistrictId(),



            $provinceId



        );



    }



    private function deliverableProvinceCacheKey(): string



    {



        return sprintf(



            'ghn:deliverable:provinces:%d:%d',



            $this->shopId,



            $this->fromDistrictId()



        );



    }



    // ─────────────────────────────────────────────────────────────────────────



    // PHÍ VẬN CHUYỂN



    // ─────────────────────────────────────────────────────────────────────────



    /**



     * Chọn service thật sự do GHN trả về cho tuyến.



     *



     * < 20kg ưu tiên service_type_id = 2



     * >= 20kg ưu tiên service_type_id = 5



     */



    private function resolveService(



        int $toDistrictId,



        int $weight



    ): array {



        $services = $this->getAvailableServicesCached($toDistrictId);



        if (empty($services)) {



            throw new RuntimeException(



                'Tuyến này chưa được GHN Sandbox hỗ trợ.'



            );



        }



        $preferredType = $weight >= 20000 ? 5 : 2;



        foreach ($services as $service) {



            $serviceId     = (int) ($service['service_id'] ?? 0);



            $serviceTypeId = (int) ($service['service_type_id'] ?? 0);



            if ($serviceId > 0 && $serviceTypeId === $preferredType) {



                return [



                    'service_id'      => $serviceId,



                    'service_type_id' => $serviceTypeId,



                ];



            }



        }



        foreach ($services as $service) {



            $serviceId     = (int) ($service['service_id'] ?? 0);



            $serviceTypeId = (int) ($service['service_type_id'] ?? 0);



            if ($serviceId > 0 && $serviceTypeId > 0) {



                return [



                    'service_id'      => $serviceId,



                    'service_type_id' => $serviceTypeId,



                ];



            }



        }



        throw new RuntimeException(



            'Không xác định được dịch vụ GHN cho tuyến giao hàng.'



        );



    }



    /**



     * Tính phí ship thật từ GHN.



     */



    public function calculateFee(



        int $toDistrictId,



        string $toWardCode,



        int $weight,



        int $insuranceValue = 0



    ): array {



        $this->ensureShippingConfigured();



        if ($toDistrictId <= 0) {



            throw new InvalidArgumentException(



                'Quận/huyện nhận hàng không hợp lệ.'



            );



        }



        if ($toWardCode === '') {



            throw new InvalidArgumentException(



                'Phường/xã nhận hàng không hợp lệ.'



            );



        }



        if ($weight <= 0) {



            throw new InvalidArgumentException(



                'Khối lượng đơn hàng phải lớn hơn 0 gram.'



            );



        }



        $service = $this->resolveService(



            $toDistrictId,



            $weight



        );



        $payload = [



            /*



             * Gửi cả 2 để tương thích response thực tế GHN Sandbox



             * của project này.



             */



            'service_id'       => $service['service_id'],



            'service_type_id'  => $service['service_type_id'],



            'from_district_id' => $this->fromDistrictId(),



            'from_ward_code'   => $this->fromWardCode(),



            'to_district_id'   => $toDistrictId,



            'to_ward_code'     => $toWardCode,



            'weight'           => $weight,



            'insurance_value'  => max(0, $insuranceValue),



        ];



        Log::info('GHN CALCULATE FEE', [



            'shop_id' => $this->shopId,



            'payload' => $payload,



        ]);



        return $this->post(



            '/shiip/public-api/v2/shipping-order/fee',



            $payload



        );



    }



    // ─────────────────────────────────────────────────────────────────────────



    // TẠO VẬN ĐƠN



    // ─────────────────────────────────────────────────────────────────────────



    /**



     * Tạo đơn hàng GHN.



     *



     * @param array $payload payload theo cấu trúc GHN API.



     */



    public function createOrder(array $payload): array



    {



        $this->ensureShippingConfigured();



        $toDistrictId = (int) ($payload['to_district_id'] ?? 0);



        $weight       = (int) ($payload['weight'] ?? 0);



        if ($toDistrictId <= 0) {



            throw new InvalidArgumentException(



                'Thiếu quận/huyện nhận hàng.'



            );



        }



        if ($weight <= 0) {



            throw new InvalidArgumentException(



                'Thiếu khối lượng đơn hàng.'



            );



        }



        $service = $this->resolveService(



            $toDistrictId,



            $weight



        );



        $default = [



            'service_id'       => $service['service_id'],



            'service_type_id'  => $service['service_type_id'],



            'from_district_id' => $this->fromDistrictId(),



            'from_ward_code'   => $this->fromWardCode(),



            'payment_type_id'  => 2,



            'required_note'    => 'KHONGCHOXEMHANG',



        ];



        return $this->post(



            '/shiip/public-api/v2/shipping-order/create',



            array_merge($default, $payload)



        );



    }



    // ─────────────────────────────────────────────────────────────────────────



    // TRA CỨU / IN / HỦY



    // ─────────────────────────────────────────────────────────────────────────



    public function trackOrder(string $ghnOrderCode): array



    {



        return $this->post(



            '/shiip/public-api/v2/shipping-order/detail',



            [



                'order_code' => $ghnOrderCode,



            ]



        );



    }



    public function getPrintUrl(array $orderCodes, string $size = 'A5'): string



    {



        $resp = $this->post(



            '/shiip/public-api/v2/a5/gen-token',



            [



                'order_codes' => $orderCodes,



            ]



        );



        Log::info('GHN gen-token response', [



            'resp'       => $resp,



            'orderCodes' => $orderCodes,



        ]);



        $printToken = $resp['token'] ?? null;



        if (!$printToken) {



            throw new RuntimeException(



                'Không lấy được print token từ GHN.'



            );



        }



        return 'https://dev-online-gateway.ghn.vn/a5/public-api/printA5?'



            . http_build_query([



                'token' => $printToken,



                'size'  => $size,



            ]);



    }



    public function cancelOrder(string $ghnOrderCode): array



    {



        return $this->post(



            '/shiip/public-api/v2/switch-status/cancel',



            [



                'order_codes' => [$ghnOrderCode],



            ]



        );



    }



    // ─────────────────────────────────────────────────────────────────────────



    // HTTP HELPERS



    // ─────────────────────────────────────────────────────────────────────────



    private function headers(): array



    {



        return [



            'Token'        => $this->token,



            'ShopId'       => (string) $this->shopId,



            'Content-Type' => 'application/json',



            'Accept'       => 'application/json',



        ];



    }



    private function get(string $path, array $query = []): array



    {



        try {



            $resp = Http::connectTimeout(5)

                ->timeout(15)

                ->retry(

                    3,

                    1000

                )

                ->withHeaders(

                    $this->headers()

                )

                ->get(

                    $this->baseUrl . $path,

                    $query

                );



            return $this->parseResponse($resp);



        } catch (\Throwable $e) {



            Log::error('GHN GET error', [



                'path'  => $path,



                'query' => $query,



                'error' => $e->getMessage(),



            ]);



            throw $e;



        }



    }



    private function post(string $path, array $body = []): array



    {



        try {



            $resp = Http::connectTimeout(5)

                ->timeout(15)

                ->retry(

                    3,

                    1000

                )

                ->withHeaders(

                    $this->headers()

                )

                ->post(

                    $this->baseUrl . $path,

                    $body

                );



            return $this->parseResponse($resp);



        } catch (\Throwable $e) {



            Log::error('GHN POST error', [



                'path'  => $path,



                'body'  => $body,



                'error' => $e->getMessage(),



            ]);



            /*



             * Không return [] ở đây.



             * Nếu nuốt exception sẽ che mất lỗi thật GHN.



             */



            throw $e;



        }



    }



    private function parseResponse(Response $resp): array



    {



        $json = $resp->json();



        if (!is_array($json)) {



            throw new RuntimeException(



                'GHN trả về response không hợp lệ.'



            );



        }



        $code = (int) ($json['code'] ?? $resp->status());



        if (!$resp->successful() || $code !== 200) {



            $message =



                $json['message']



                ?? $json['code_message_value']



                ?? $json['code_message']



                ?? 'Lỗi không xác định từ GHN.';



            Log::warning('GHN API error', [



                'http_status'       => $resp->status(),



                'code'              => $json['code'] ?? null,



                'code_message'      => $json['code_message'] ?? null,



                'code_message_value'=> $json['code_message_value'] ?? null,



                'message'           => $json['message'] ?? null,



            ]);



            throw new RuntimeException(



                'GHN: ' . $message



            );



        }



        /*



         * GHN available-services có thể trả:



         * code=200, data=null, message=Success



         * => hiểu là tuyến không có service.



         */



        $data = $json['data'] ?? [];



        return is_array($data) ? $data : [];



    }



}