<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'age_from',
        'age_to',
        'description',
        'icon',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'age_from' => 'integer',
        'age_to' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}