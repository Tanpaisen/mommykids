<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Temporary controller for modules referenced in the sidebar but not yet built
 * (Stages, Articles, Comments, Orders, Shipments, Refunds, Clients, Vouchers, Banners).
 * Replace each route in routes/admin.php with a dedicated controller as that module is implemented.
 */
class PlaceholderController extends Controller
{
    public function index(string $title = 'Đang phát triển')
    {
        return view('admin.placeholder', ['title' => $title]);
    }
}
