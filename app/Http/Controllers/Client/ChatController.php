<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct(private readonly ChatBotService $bot) {}

    public function session(Request $request): JsonResponse
    {
        $conversation = $this->findOrCreateConversation();
        return response()->json($this->conversationPayload($conversation));
    }

    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->assertCustomerOwns($conversation);
        return response()->json($this->conversationPayload($conversation));
    }

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->assertCustomerOwns($conversation);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        if ($conversation->status === ChatConversation::STATUS_CLOSED) {
            return response()->json(['message' => 'Phiên trò chuyện đã kết thúc.'], 422);
        }

        DB::transaction(function () use ($conversation, $data) {
            $locked = ChatConversation::query()->lockForUpdate()->findOrFail($conversation->id);

            $this->createMessage(
                $locked,
                ChatMessage::SENDER_CUSTOMER,
                Auth::id() ? (string) Auth::id() : null,
                trim($data['message'])
            );

            if ($locked->status !== ChatConversation::STATUS_BOT) {
                return;
            }

            $history = $conversation->messages()
    ->reorder()
    ->where(
        'sender_type',
        ChatMessage::SENDER_CUSTOMER
    )
    ->latest('created_at')
    ->latest('id')
    ->limit(10)
    ->pluck('message')
    ->reverse()
    ->values()
    ->all();

$reply = $this->bot->respond(
    $data['message'],
    $history
);

            if ($reply['request_staff']) {
                $locked->forceFill([
                    'status' => ChatConversation::STATUS_WAITING_STAFF,
                    'bot_fail_count' => 0,
                ])->save();

                $this->createMessage(
                    $locked,
                    ChatMessage::SENDER_BOT,
                    null,
                    'Mình sẽ kết nối bạn với chuyên viên MommyKids. Vui lòng chờ trong giây lát.'
                );
                return;
            }

            if ($reply['understood']) {
                $locked->forceFill(['bot_fail_count' => 0])->save();
                $this->createMessage($locked, ChatMessage::SENDER_BOT, null, $reply['message']);
                return;
            }

            $failCount = (int) $locked->bot_fail_count + 1;

            if ($failCount >= 2) {
                $locked->forceFill([
                    'status' => ChatConversation::STATUS_WAITING_STAFF,
                    'bot_fail_count' => $failCount,
                ])->save();

                $this->createMessage(
                    $locked,
                    ChatMessage::SENDER_BOT,
                    null,
                    'Mình vẫn chưa hiểu chính xác yêu cầu. Mình đã chuyển cuộc trò chuyện sang hàng chờ để chuyên viên MommyKids hỗ trợ bạn.'
                );
                return;
            }

            $locked->forceFill(['bot_fail_count' => $failCount])->save();
            $this->createMessage($locked, ChatMessage::SENDER_BOT, null, $reply['message']);
        });

        return response()->json($this->conversationPayload($conversation->fresh()));
    }

    public function requestStaff(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->assertCustomerOwns($conversation);

        if ($conversation->status === ChatConversation::STATUS_CLOSED) {
            return response()->json(['message' => 'Phiên trò chuyện đã kết thúc.'], 422);
        }

        DB::transaction(function () use ($conversation) {
            $locked = ChatConversation::query()->lockForUpdate()->findOrFail($conversation->id);

            if ($locked->status === ChatConversation::STATUS_BOT) {
                $locked->forceFill([
                    'status' => ChatConversation::STATUS_WAITING_STAFF,
                    'bot_fail_count' => 0,
                ])->save();

                $this->createMessage(
                    $locked,
                    ChatMessage::SENDER_BOT,
                    null,
                    'MommyKids đã nhận yêu cầu. Đang kết nối bạn với chuyên viên...'
                );
            }
        });

        return response()->json($this->conversationPayload($conversation->fresh()));
    }

    public function newSession(Request $request): JsonResponse
    {
        $open = $this->findOpenConversation();

        if ($open) {
            return response()->json($this->conversationPayload($open));
        }

        $conversation = $this->createConversation();
        return response()->json($this->conversationPayload($conversation), 201);
    }

    private function findOrCreateConversation(): ChatConversation
    {
        if (Auth::check()) {
            $conversation = ChatConversation::query()
                ->where('user_id', Auth::id())
                ->whereIn('status', [
                    ChatConversation::STATUS_BOT,
                    ChatConversation::STATUS_WAITING_STAFF,
                    ChatConversation::STATUS_STAFF_CONNECTED,
                ])
                ->latest('last_message_at')
                ->first();

            if ($conversation) {
                return $conversation;
            }

            $guestToken = session('chat_guest_token');

            if ($guestToken) {
                $guestConversation = ChatConversation::query()
                    ->whereNull('user_id')
                    ->where('guest_token', $guestToken)
                    ->whereIn('status', [
                        ChatConversation::STATUS_BOT,
                        ChatConversation::STATUS_WAITING_STAFF,
                        ChatConversation::STATUS_STAFF_CONNECTED,
                    ])
                    ->latest('last_message_at')
                    ->first();

                if ($guestConversation) {
                    $guestConversation->forceFill([
                        'user_id' => Auth::id(),
                        'guest_token' => null,
                    ])->save();
                    return $guestConversation;
                }
            }
        }

        return $this->findOpenConversation() ?? $this->createConversation();
    }

    private function findOpenConversation(): ?ChatConversation
    {
        if (Auth::check()) {
            return ChatConversation::query()
                ->where('user_id', Auth::id())
                ->whereIn('status', [
                    ChatConversation::STATUS_BOT,
                    ChatConversation::STATUS_WAITING_STAFF,
                    ChatConversation::STATUS_STAFF_CONNECTED,
                ])
                ->latest('last_message_at')
                ->first();
        }

        $guestToken = session('chat_guest_token');
        if (!$guestToken) return null;

        return ChatConversation::query()
            ->whereNull('user_id')
            ->where('guest_token', $guestToken)
            ->whereIn('status', [
                ChatConversation::STATUS_BOT,
                ChatConversation::STATUS_WAITING_STAFF,
                ChatConversation::STATUS_STAFF_CONNECTED,
            ])
            ->latest('last_message_at')
            ->first();
    }

    private function createConversation(): ChatConversation
    {
        $guestToken = null;

        if (!Auth::check()) {
            $guestToken = session('chat_guest_token');

            if (!$guestToken) {
                $guestToken = (string) Str::ulid();
                session(['chat_guest_token' => $guestToken]);
            }
        }

        $conversation = ChatConversation::create([
            'user_id' => Auth::id(),
            'guest_token' => $guestToken,
            'status' => ChatConversation::STATUS_BOT,
            'bot_fail_count' => 0,
            'started_at' => now(),
            'last_message_at' => now(),
        ]);

        $this->createMessage(
            $conversation,
            ChatMessage::SENDER_BOT,
            null,
            'Xin chào 👋 Mình là trợ lý MommyKids. Mình có thể hỗ trợ tư vấn sản phẩm, đơn hàng, voucher, vận chuyển hoặc kết nối bạn với nhân viên.'
        );

        return $conversation;
    }

    private function assertCustomerOwns(ChatConversation $conversation): void
    {
        if (Auth::check() && (string) $conversation->user_id === (string) Auth::id()) {
            return;
        }

        $guestToken = (string) session('chat_guest_token', '');

        abort_unless(
            $conversation->user_id === null
            && $guestToken !== ''
            && $conversation->guest_token !== null
            && hash_equals((string) $conversation->guest_token, $guestToken),
            403
        );
    }

    private function createMessage(
        ChatConversation $conversation,
        string $senderType,
        ?string $senderId,
        string $message
    ): ChatMessage {
        $chatMessage = $conversation->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message' => $message,
            'message_type' => 'text',
            'is_read' => false,
        ]);

        $conversation->forceFill(['last_message_at' => now()])->save();
        return $chatMessage;
    }

    private function conversationPayload(ChatConversation $conversation): array
    {
        $conversation->loadMissing(['staff:id,name']);

        $messages = $conversation->messages()
        ->reorder()
        ->with('sender:id,name')
        ->latest('created_at')
        ->latest('id')
        ->limit(100)
        ->get()
        ->reverse()
        ->values();

        return [
            'conversation' => [
                'id' => (string) $conversation->id,
                'status' => $conversation->status,
                'bot_fail_count' => (int) $conversation->bot_fail_count,
                'staff' => $conversation->staff ? [
                    'id' => (string) $conversation->staff->id,
                    'name' => $conversation->staff->name,
                ] : null,
                'closed_at' => $conversation->closed_at?->toIso8601String(),
            ],
            'messages' => $messages->map(fn (ChatMessage $message) => [
                'id' => (string) $message->id,
                'sender_type' => $message->sender_type,
                'sender_name' => $this->senderName($message),
                'message' => $message->message,
                'created_at' => $message->created_at?->toIso8601String(),
                'time' => $message->created_at?->format('H:i'),
            ])->all(),
        ];
    }

    private function senderName(ChatMessage $message): string
    {
        return match ($message->sender_type) {
            ChatMessage::SENDER_CUSTOMER => 'Bạn',
            ChatMessage::SENDER_BOT => 'MommyKids Bot',
            ChatMessage::SENDER_STAFF => $message->sender?->name ?? 'Nhân viên MommyKids',
            ChatMessage::SENDER_SYSTEM => 'Hệ thống',
            default => 'MommyKids',
        };
    }
}
