<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Trang xem tất cả thông báo.
     * Mỗi trang chỉ lấy 20 bản ghi.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view(
            'notifications.index',
            compact('notifications')
        );
    }

    /**
     * Lấy 8 thông báo mới nhất cho dropdown header.
     *
     * Dữ liệu được cache Redis trong User model.
     */
    public function latest(Request $request)
    {
        /** @var User $user */
        $user = $request->user();

        $notifications =
            $user->cachedHeaderNotifications();

        return response()->json([
            'unread_count' =>
                $user->cachedUnreadCount(),

            'data' =>
                $notifications
                    ->map(function ($notification) {
                        return [
                            'id' =>
                                (string) $notification->id,

                            'title' =>
                                $notification->data['title']
                                ?? '',

                            'body' =>
                                $notification->data['body']
                                ?? '',

                            'action_url' =>
                                $notification->data['action_url']
                                ?? null,

                            'read' =>
                                $notification->read_at
                                !== null,

                            'created_at' =>
                                $notification
                                    ->created_at
                                    ?->diffForHumans()
                                ?? '',
                        ];
                    })
                    ->values(),
        ]);
    }

    /**
     * Số thông báo chưa đọc trên badge chuông.
     *
     * Không query DB mỗi 30 giây nữa.
     * Ưu tiên đọc Redis.
     */
    public function unreadCount(
        Request $request
    ) {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'unread_count' =>
                $user->cachedUnreadCount(),
        ]);
    }

    /**
     * Bấm một thông báo:
     * - đánh dấu đã đọc;
     * - xóa cache header;
     * - chuyển tới URL liên quan.
     */
    public function read(
        Request $request,
        string $id
    ) {
        /** @var User $user */
        $user = $request->user();

        $notification =
            $user->notifications()
                ->findOrFail($id);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        $this->clearHeaderNotificationCache(
            (string) $user->id
        );

        return redirect(
            $notification->data['action_url']
            ?? route('notifications.index')
        );
    }

    /**
     * Đánh dấu tất cả là đã đọc.
     */
    public function readAll(
        Request $request
    ) {
        /** @var User $user */
        $user = $request->user();

        $user->unreadNotifications
            ->markAsRead();

        $this->clearHeaderNotificationCache(
            (string) $user->id
        );

        return back();
    }

    /**
     * Xóa một thông báo.
     */
    public function destroy(
        Request $request,
        string $id
    ) {
        /** @var User $user */
        $user = $request->user();

        $notification =
            $user->notifications()
                ->findOrFail($id);

        $notification->delete();

        $this->clearHeaderNotificationCache(
            (string) $user->id
        );

        return back();
    }

    /**
     * Xóa cache thông báo header.
     *
     * Request tiếp theo sẽ query DB 1 lần
     * rồi cache lại vào Redis.
     */
    private function clearHeaderNotificationCache(
        string $userId
    ): void {
        Cache::forget(
            User::unreadCacheKey(
                $userId
            )
        );

        Cache::forget(
            User::notificationListCacheKey(
                $userId
            )
        );
    }

    /**
     * Trang tùy chọn thông báo.
     */
    public function preferences(
        Request $request
    ) {
        $prefs =
            $request->user()
                ->notificationPreferences
                ->keyBy('type');

        return view(
            'notifications.preferences',
            compact('prefs')
        );
    }

    /**
     * Cập nhật tùy chọn nhận thông báo.
     */
    public function updatePreferences(
        Request $request
    ) {
        /** @var User $user */
        $user = $request->user();

        /*
         * Đơn hàng:
         * thông báo trong tài khoản luôn bật,
         * khách chỉ bật/tắt email.
         */
        $user->notificationPreferences()
            ->updateOrCreate(
                [
                    'type' =>
                        'order_status',
                ],
                [
                    'database' => true,
                    'mail' =>
                        $request->boolean(
                            'order_mail'
                        ),
                ]
            );

        /*
         * Marketing:
         * khách có thể chọn cả database và mail.
         */
        $user->notificationPreferences()
            ->updateOrCreate(
                [
                    'type' =>
                        'marketing',
                ],
                [
                    'database' =>
                        $request->boolean(
                            'marketing_database'
                        ),

                    'mail' =>
                        $request->boolean(
                            'marketing_mail'
                        ),
                ]
            );

        return back()->with(
            'status',
            'Đã lưu cài đặt thông báo'
        );
    }
}