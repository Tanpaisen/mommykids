<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GHNService
{
    protected string $baseUrl;
    protected string $token;
    protected int $shopId;

    public function __construct()
    {
        $this->baseUrl = config('services.ghn.base_url');
        $this->token   = config('services.ghn.token');
        $this->shopId  = config('services.ghn.shop_id');
    }

    protected function headers(): array
    {
        return [
            'Token'        => $this->token,
            'ShopId'       => (string) $this->shopId,
            'Content-Type' => 'application/json',
        ];
    }

    public function createOrder(array $data): array
    {
        $res = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/v2/shipping-order/create", $data);

        return $res->json('data', []);
    }

    public function trackOrder(string $orderCode): array
    {
        $res = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/v2/shipping-order/detail", [
                'order_code' => $orderCode,
            ]);

        return $res->json('data', []);
    }

    public function calculateFee(
        int $toDistrictId,
        string $toWardCode,
        int $weight = 500,
        int $insuranceValue = 0
    ): array {
        $res = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/v2/shipping-order/fee", [
                'to_district_id'  => $toDistrictId,
                'to_ward_code'    => $toWardCode,
                'weight'          => $weight,
                'insurance_value' => $insuranceValue,
                'service_type_id' => 2,
            ]);

        return $res->json('data', []);
    }

    public function getPrintUrl(array $orderCodes): string
    {
        $res = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/v2/a5/gen-token", [
                'order_codes' => $orderCodes,
            ]);

        $token = $res->json('data.token');

        if (!$token) {
            throw new RuntimeException('Không lấy được token in vận đơn.');
        }

        return "https://dev-online-gateway.ghn.vn/a5/public-api/print?token={$token}";
    }

    public function cancelOrder(string $orderCode): array
    {
        $res = Http::withHeaders($this->headers())
            ->post("{$this->baseUrl}/v2/switch-status/cancel", [
                'order_codes' => [$orderCode],
            ]);

        return $res->json('data', []);
    }
}