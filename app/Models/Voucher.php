<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'is_public' => 'boolean',
        'auto_apply' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function conditions()
    {
        return $this->hasMany(VoucherCondition::class);
    }

    public function allowedUsers()
    {
        return $this->belongsToMany(User::class, 'voucher_users');
    }

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }
}
