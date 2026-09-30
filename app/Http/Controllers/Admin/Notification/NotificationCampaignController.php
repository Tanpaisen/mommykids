<?php

namespace App\Http\Controllers\Admin\Notification;

use App\Http\Controllers\Controller;
use App\Jobs\SendMarketingCampaign;
use App\Models\NotificationCampaign;
use App\Notifications\MarketingNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NotificationCampaignController extends Controller
{
    public function index()
    {
        $campaigns = NotificationCampaign::query()
            ->withCount([
                'logs as sent_count'   => fn ($q) => $q->where('status', 'sent'),
                'logs as failed_count' => fn ($q) => $q->where('status', 'failed'),
            ])
            ->latest()->paginate(20);

        return view('admin.notification.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.notification.campaigns.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:120',
            'body'       => 'required|string|max:1000',
            'action_url' => ['nullable', 'string', 'max:255', 'regex:#^/(?!/)#'], // chỉ link nội bộ
            'channels'   => 'required|array|min:1',
            'channels.*' => 'in:in_app,mail',
        ]);

        NotificationCampaign::create([
            'title'       => $data['title'],
            'body'        => $data['body'],
            'action_url'  => $data['action_url'] ?? null,
            'send_in_app' => in_array('in_app', $data['channels']),
            'send_mail'   => in_array('mail', $data['channels']),
            'created_by'  => (string) $request->user()->id,
        ]);

        return redirect()->route('admin.notification-campaigns.index')->with('success', 'Đã lưu bản nháp.');
    }

    public function test(Request $request, NotificationCampaign $campaign)
    {
        Notification::route('mail', $request->user()->email)->notify(new MarketingNotification($campaign));

        return back()->with('success', 'Đã gửi thử tới ' . $request->user()->email);
    }

    public function send(NotificationCampaign $campaign)
    {
        // Cập nhật nguyên tử: bấm hai lần chỉ có một lần được gửi
        $claimed = NotificationCampaign::whereKey($campaign->id)
            ->where('status', 'draft')->update(['status' => 'sending']);

        if (! $claimed) {
            return back()->with('error', 'Chiến dịch này đã được gửi.');
        }

        SendMarketingCampaign::dispatch($campaign->id)->onQueue('marketing');

        return back()->with('success', 'Đang gửi chiến dịch.');
    }

        public function reset(NotificationCampaign $campaign)
    {
        if ($campaign->status !== 'sending') {
            return back()->with('error', 'Chỉ có thể đặt lại chiến dịch đang ở trạng thái "sending".');
        }

        $campaign->update(['status' => 'draft']);

        return back()->with('success', 'Đã đặt lại về bản nháp, có thể gửi lại.');
    }

    public function duplicate(NotificationCampaign $campaign)
    {
        $new = $campaign->replicate();
        $new->status = 'draft';
        $new->recipients_count = 0;
        $new->sent_at = null;
        $new->save();

        return redirect()->route('admin.notification-campaigns.index')->with('success', 'Đã tạo bản sao, chỉnh sửa và gửi khi sẵn sàng.');
    }
}