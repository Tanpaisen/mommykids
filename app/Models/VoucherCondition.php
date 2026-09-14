<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherCondition extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_include' => 'boolean',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}