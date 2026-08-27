<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    private string $baseUrl;
    private string $token;
    private int    $shopId;

    public function __construct()
    {
        $this->baseUrl = config('ghn.base_url');
        $this->token   = config('ghn.token');
        $this->shopId  = (int) config('ghn.shop_id');
    }

    // ─── Địa chỉ ─────────────────────────────────────────────────────────────

    /** Lấy danh sách tỉnh/thành */
    public function getProvinces(): array
    {
        return $this->get('/shiip/public-api/master-data/province');
    }

    /** Lấy danh sách quận/huyện theo tỉnh */
    public function getDistricts(int $provinceId): array
    {
        return $this->post('/shiip/public-api/master-data/district', [
            'province_id' => $provinceId,
        ]);
    }

    /** Lấy danh sách phường/xã theo quận */
    public function getWards(int $districtId): array
    {
        return $this->post('/shiip/public-api/master-data/ward', [
            'district_id' => $districtId,
        ]);
    }

    // ─── Phí vận chuyển ──────────────────────────────────────────────────────

    /**
     * Tính phí ship
     *
     * @param int $toDistrictId
     * @param string $toWardCode
     * @param int $weight gram
     * @param int $insuranceValue giá trị bảo hiểm (đồng)
     */
    public function calculateFee(
        int    $toDistrictId,
        string $toWardCode,
        int    $weight = 500,
        int    $insuranceValue = 0
    ): array {
        return $this->post('/shiip/public-api/v2/shipping-order/fee', [
            'service_type_id' => 2, // 2 = GHN standard (Giao hàng nhanh)
            'from_district_id'=> (int) config('ghn.from_district_id'),
            'to_district_id'  => $toDistrictId,
            'to_ward_code'    => $toWardCode,
            'weight'          => $weight,
            'insurance_value' => $insuranceValue,
        ]);
    }

    // ─── Tạo vận đơn ─────────────────────────────────────────────────────────

    /**
     * Tạo đơn hàng GHN
     *
     * @param array $payload theo cấu trúc GHN API
     */
    public function createOrder(array $payload): array
    {
        $default = [
            'service_type_id'   => 2,
            'from_district_id'  => (int) config('ghn.from_district_id'),
            'from_ward_code'    => (string) config('ghn.from_ward_code'), // <-- BỔ SUNG DÒNG NÀY
            'payment_type_id'   => 2,   // 1=người gửi trả, 2=người nhận trả (COD)
            'required_note'     => 'KHONGCHOXEMHANG',
        ];

        return $this->post(
            '/shiip/public-api/v2/shipping-order/create',
            array_merge($default, $payload)
        );
    }

    // ─── Tra cứu trạng thái ──────────────────────────────────────────────────

    /** Tra cứu trạng thái vận đơn */
    public function trackOrder(string $ghnOrderCode): array
    {
        return $this->post('/shiip/public-api/v2/shipping-order/detail', [
            'order_code' => $ghnOrderCode,
        ]);
    }

    // ─── In vận đơn ──────────────────────────────────────────────────────────

    /**
     * Lấy URL in vận đơn (A5/A6/80x80)
     * GHN trả về URL PDF trực tiếp
     */
    public function getPrintUrl(array $orderCodes, string $size = 'A5'): string
    {
        $query = http_build_query([
            'token'  => $this->token,
            'size'   => $size,
        ]);

        // GHN dùng query-string + body khác nhau tuỳ version
        // Endpoint print token
        $resp = $this->post('/shiip/public-api/v2/a5/gen-token', [
            'order_codes' => $orderCodes,
        ]);

        $printToken = $resp['token'] ?? null;

        if (! $printToken) {
            throw new \RuntimeException('Không lấy được print token từ GHN: ' . json_encode($resp));
        }

        return "https://dev-online-gateway.ghn.vn/a5/public-api/print?" .
               http_build_query(['token' => $printToken]);
    }

    // ─── Huỷ đơn ─────────────────────────────────────────────────────────────

    public function cancelOrder(string $ghnOrderCode): array
    {
        return $this->post('/shiip/public-api/v2/switch-status/cancel', [
            'order_codes' => [$ghnOrderCode],
        ]);
    }

    // ─── HTTP helpers ─────────────────────────────────────────────────────────

    private function headers(): array
    {
        return [
            'Token'       => $this->token,
            'ShopId'      => $this->shopId,
            'Content-Type'=> 'application/json',
        ];
    }

    private function get(string $path): array
    {
        try {
            $resp = Http::withHeaders($this->headers())
                ->get($this->baseUrl . $path);
            return $this->parseResponse($resp);
        } catch (\Throwable $e) {
            Log::error('GHN GET error', ['path' => $path, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function post(string $path, array $body = []): array
    {
        try {
            $resp = Http::withHeaders($this->headers())
                ->post($this->baseUrl . $path, $body);
            return $this->parseResponse($resp);
        } catch (\Throwable $e) {
            Log::error('GHN POST error', ['path' => $path, 'error' => $e->getMessage()]);
            return [];
        }
    }

    private function parseResponse(Response $resp): array
    {
        $json = $resp->json();

        if (($json['code'] ?? null) !== 200) {
            Log::warning('GHN API non-200', [
                'code'    => $json['code'] ?? null,
                'message' => $json['message'] ?? null,
            ]);
        }

        return $json['data'] ?? $json;
    }
}