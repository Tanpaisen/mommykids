<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCondition extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_include' => 'boolean',
        'payload'    => 'array', // Tự động chuyển JSON ↔ mảng PHP
    ];

    /**
     * Voucher cha chứa điều kiện này
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    /**
     * Tách chuỗi giá trị phân tách dấu phẩy thành mảng
     * Ví dụ: "prod_abc,prod_xyz" → ['prod_abc', 'prod_xyz']
     */
    public function getValuesAsArray(): array
    {
        if (empty($this->value)) {
            return [];
        }

        return array_map('trim', explode(',', (string)$this->value));
    }

    /**
     * Lấy dữ liệu từ cột mở rộng payload
     * Dùng cho điều kiện phức tạp: khung giờ, phương thức thanh toán...
     */
    public function getPayloadValue(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }

    /**
     * Kiểm tra giá trị đầu vào có thỏa mãn điều kiện không
     * Tự đảo ngược logic khi is_include = false
     */
    public function matches(mixed $inputValue): bool
    {
        $input = (string)$inputValue;
        $condValues = $this->getValuesAsArray();

        $result = match ($this->operator) {
            'eq'     => in_array($input, $condValues, true),
            'neq'    => !in_array($input, $condValues, true),
            'in'     => in_array($input, $condValues, true),
            'not_in' => !in_array($input, $condValues, true),
            'gte'    => is_numeric($input) && is_numeric($this->value) && $input >= $this->value,
            'lte'    => is_numeric($input) && is_numeric($this->value) && $input <= $this->value,
            default  => false,
        };

        return $this->is_include ? $result : !$result;
    }
}