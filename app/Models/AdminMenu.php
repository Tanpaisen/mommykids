<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
    protected $fillable = ['group_name', 'title', 'route_name', 'order', 'is_active'];
}