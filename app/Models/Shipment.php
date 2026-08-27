<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'order_id','ghn_order_code','tracking_number','carrier',
        'service_type','shipping_fee','weight','length','width','height',
        'status','expected_delivery_at','ghn_response',
    ];

    protected $casts = [
        'ghn_response'        => 'array',
        'expected_delivery_at'=> 'datetime',
    ];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}