<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    // JSON nhỏ cho dropdown chuông (dùng fetch, không cần F5)
    public function latest(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $user->notifications()->limit(8)->get()->map(fn ($n) => [
                'id'         => $n->id,
                'title'      => $n->data['title'] ?? '',
                'body'       => $n->data['body'] ?? '',
                'read'       => $n->read_at !== null,
                'created_at' => $n->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    // Bấm vào thông báo: đánh dấu đã đọc rồi chuyển tới trang liên quan
    public function read(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect($notification->data['action_url'] ?? route('notifications.index'));
    }

    public function readAll(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back();
    }

    public function destroy(Request $request, string $id)
    {
        $request->user()->notifications()->findOrFail($id)->delete();

        return back();
    }

    public function preferences(Request $request)
{
    $prefs = $request->user()->notificationPreferences->keyBy('type');

    return view('notifications.preferences', compact('prefs'));
}

    public function updatePreferences(Request $request)
    {
        $user = $request->user();

        // Đơn hàng: thông báo trong tài khoản luôn bật, khách chỉ chọn được email
        $user->notificationPreferences()->updateOrCreate(
            ['type' => 'order_status'],
            ['database' => true, 'mail' => $request->boolean('order_mail')]
        );

        // Khuyến mãi: khách chọn cả hai kênh
        $user->notificationPreferences()->updateOrCreate(
            ['type' => 'marketing'],
            [
                'database' => $request->boolean('marketing_database'),
                'mail'     => $request->boolean('marketing_mail'),
            ]
        );

        return back()->with('status', 'Đã lưu cài đặt thông báo');
    }
}