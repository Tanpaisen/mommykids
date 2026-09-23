<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatBotScenario;
use App\Models\ChatConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerCareController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->string('tab')->toString();

        if (!in_array($tab, ['scenarios', 'live'], true)) {
            $tab = 'scenarios';
        }

        $adminId = Auth::guard('admin')->id();

        /*
        |--------------------------------------------------------------------------
        | Kịch bản Chat Bot
        |--------------------------------------------------------------------------
        */
        $scenarios = ChatBotScenario::query()
            ->orderBy('priority')
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Khách đang chờ nhân viên
        |--------------------------------------------------------------------------
        */
        $waitingConversations = ChatConversation::query()
            ->with([
                'user:id,name,email',
                'staff:id,name',
            ])
            ->withCount('messages')
            ->where(
                'status',
                ChatConversation::STATUS_WAITING_STAFF
            )
            ->latest('last_message_at')
            ->limit(20)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Phiên đang được Admin hiện tại tư vấn
        |--------------------------------------------------------------------------
        */
        $myConversations = ChatConversation::query()
            ->with([
                'user:id,name,email',
                'staff:id,name',
            ])
            ->withCount('messages')
            ->where(
                'status',
                ChatConversation::STATUS_STAFF_CONNECTED
            )
            ->where('staff_id', $adminId)
            ->latest('last_message_at')
            ->limit(20)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Thống kê
        |--------------------------------------------------------------------------
        */
        $stats = [
            'scenario_total' =>
                $scenarios->count(),

            'scenario_active' =>
                $scenarios
                    ->where('is_active', true)
                    ->count(),

            'waiting' =>
                ChatConversation::query()
                    ->where(
                        'status',
                        ChatConversation::STATUS_WAITING_STAFF
                    )
                    ->count(),

            'mine' =>
                ChatConversation::query()
                    ->where(
                        'status',
                        ChatConversation::STATUS_STAFF_CONNECTED
                    )
                    ->where('staff_id', $adminId)
                    ->count(),

            'connected' =>
                ChatConversation::query()
                    ->where(
                        'status',
                        ChatConversation::STATUS_STAFF_CONNECTED
                    )
                    ->count(),

            'closed_today' =>
                ChatConversation::query()
                    ->where(
                        'status',
                        ChatConversation::STATUS_CLOSED
                    )
                    ->whereDate(
                        'closed_at',
                        today()
                    )
                    ->count(),
        ];

        return view(
            'admin.customer-care.index',
            compact(
                'tab',
                'scenarios',
                'waitingConversations',
                'myConversations',
                'stats'
            )
        );
    }
}