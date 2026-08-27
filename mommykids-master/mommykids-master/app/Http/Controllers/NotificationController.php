<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    public function index()
    {
        // Replace with real query, e.g. auth()->user()->notifications
        return view('client.notifications', [
            'notifications' => [],
        ]);
    }
}
