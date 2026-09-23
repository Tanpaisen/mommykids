<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin, 403);

        $tab = $request->string('tab')->toString() ?: 'waiting';

        $query = ChatConversation::query()
            ->with([
                'user:id,name,email',
                'staff:id,name',
            ])
            ->withCount('messages')
            ->latest('last_message_at');

        match ($tab) {
            'mine' => $query
                ->where('staff_id', $admin->id)
                ->where(
                    'status',
                    ChatConversation::STATUS_STAFF_CONNECTED
                ),

            'bot' => $query->where(
                'status',
                ChatConversation::STATUS_BOT
            ),

            'all' => null,

            default => $query->where(
                'status',
                ChatConversation::STATUS_WAITING_STAFF
            ),
        };

        $conversations = $query
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'waiting' => ChatConversation::where(
                'status',
                ChatConversation::STATUS_WAITING_STAFF
            )->count(),

            'mine' => ChatConversation::where(
                'staff_id',
                $admin->id
            )
                ->where(
                    'status',
                    ChatConversation::STATUS_STAFF_CONNECTED
                )
                ->count(),

            'bot' => ChatConversation::where(
                'status',
                ChatConversation::STATUS_BOT
            )->count(),

            'all' => ChatConversation::count(),
        ];

        return view(
            'admin.chats.index',
            compact(
                'conversations',
                'counts',
                'tab'
            )
        );
    }

    public function show(
        ChatConversation $conversation
    ): View {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin, 403);

        $conversation->load([
            'user:id,name,email',
            'staff:id,name',
        ]);

        $messages = $conversation
            ->messages()
            ->with([
                'sender:id,name',
                'admin:id,name',
            ])
            ->get();

        return view(
            'admin.chats.show',
            compact(
                'conversation',
                'messages'
            )
        );
    }

    public function messages(
        ChatConversation $conversation
    ): JsonResponse {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin, 403);

        $conversation->messages()
            ->where(
                'sender_type',
                ChatMessage::SENDER_CUSTOMER
            )
            ->where('is_read', false)
            ->update([
                'is_read' => true,
            ]);

        $conversation->load([
            'user:id,name,email',
            'staff:id,name',
        ]);

        $messages = $conversation
            ->messages()
            ->reorder()
            ->with([
                'sender:id,name',
                'admin:id,name',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(150)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'conversation' => [
                'id' => (string) $conversation->id,
                'status' => $conversation->status,

                'customer_name' =>
                    $conversation->user?->name
                    ?? 'Khách vãng lai',

                'staff_name' =>
                    $conversation->staff?->name,
            ],

            'messages' => $messages
                ->map(
                    fn (ChatMessage $message) => [
                        'id' => (string) $message->id,

                        'sender_type' =>
                            $message->sender_type,

                        'sender_name' => match (
                            $message->sender_type
                        ) {
                            ChatMessage::SENDER_CUSTOMER =>
                                $conversation->user?->name
                                ?? 'Khách',

                            ChatMessage::SENDER_BOT =>
                                'MommyKids Bot',

                            ChatMessage::SENDER_STAFF =>
                                $message->admin?->name
                                ?? 'Nhân viên',

                            default =>
                                'Hệ thống',
                        },

                        'message' =>
                            $message->message,

                        'time' =>
                            $message->created_at
                                ?->format('H:i'),
                    ]
                )
                ->all(),
        ]);
    }

    public function accept(
        ChatConversation $conversation
    ): RedirectResponse {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin, 403);

        DB::transaction(
            function () use (
                $conversation,
                $admin
            ) {
                $locked = ChatConversation::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $conversation->id
                    );

                if (
                    $locked->status
                    === ChatConversation::STATUS_STAFF_CONNECTED
                    &&
                    (string) $locked->staff_id
                    !== (string) $admin->id
                ) {
                    abort(
                        409,
                        'Cuộc trò chuyện đã được nhân viên khác tiếp nhận.'
                    );
                }

                if (
                    $locked->status
                    === ChatConversation::STATUS_CLOSED
                ) {
                    abort(
                        422,
                        'Phiên này đã kết thúc.'
                    );
                }

                if (
                    $locked->status
                    !== ChatConversation::STATUS_WAITING_STAFF
                ) {
                    abort(
                        422,
                        'Phiên này không còn ở trạng thái chờ tiếp nhận.'
                    );
                }

                $locked->forceFill([
                    'status' =>
                        ChatConversation::STATUS_STAFF_CONNECTED,

                    'staff_id' =>
                        $admin->id,

                    'accepted_at' =>
                        now(),
                ])->save();

                $this->createMessage(
                    $locked,
                    ChatMessage::SENDER_SYSTEM,
                    null,
                    'Chuyên viên '
                    . $admin->name
                    . ' đã tiếp nhận cuộc trò chuyện.'
                );

                $this->createMessage(
                    $locked,
                    ChatMessage::SENDER_STAFF,
                    (string) $admin->id,
                    'Xin chào, mình là '
                    . $admin->name
                    . ' từ MommyKids. Mình đang trực tiếp hỗ trợ bạn.'
                );
            }
        );

        return redirect()
            ->route(
                'admin.chats.show',
                $conversation
            )
            ->with(
                'success',
                'Đã tiếp nhận cuộc trò chuyện.'
            );
    }

    public function sendMessage(
        Request $request,
        ChatConversation $conversation
    ): JsonResponse {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin, 403);

        $data = $request->validate([
            'message' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        if (
            $conversation->status
            !== ChatConversation::STATUS_STAFF_CONNECTED
        ) {
            return response()->json([
                'message' =>
                    'Cuộc trò chuyện chưa được tiếp nhận hoặc đã kết thúc.',
            ], 422);
        }

        if (
            (string) $conversation->staff_id
            !== (string) $admin->id
        ) {
            return response()->json([
                'message' =>
                    'Cuộc trò chuyện đang thuộc nhân viên khác.',
            ], 403);
        }

        $this->createMessage(
            $conversation,
            ChatMessage::SENDER_STAFF,
            (string) $admin->id,
            trim($data['message'])
        );

        return $this->messages(
            $conversation->fresh()
        );
    }

    public function close(
        ChatConversation $conversation
    ): RedirectResponse {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin, 403);

        if (
            $conversation->status
            === ChatConversation::STATUS_CLOSED
        ) {
            return back()->with(
                'success',
                'Phiên đã được đóng trước đó.'
            );
        }

        if (
            $conversation->staff_id !== null
            &&
            (string) $conversation->staff_id
            !== (string) $admin->id
        ) {
            abort(
                403,
                'Cuộc trò chuyện đang thuộc nhân viên khác.'
            );
        }

        DB::transaction(
            function () use ($conversation) {
                $locked = ChatConversation::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $conversation->id
                    );

                $locked->forceFill([
                    'status' =>
                        ChatConversation::STATUS_CLOSED,

                    'closed_at' =>
                        now(),
                ])->save();

                $this->createMessage(
                    $locked,
                    ChatMessage::SENDER_SYSTEM,
                    null,
                    'Cuộc trò chuyện đã kết thúc. Cảm ơn bạn đã liên hệ MommyKids.'
                );
            }
        );

        return redirect()
            ->route(
                'admin.chats.index',
                ['tab' => 'mine']
            )
            ->with(
                'success',
                'Đã đóng phiên trò chuyện.'
            );
    }

    private function createMessage(
        ChatConversation $conversation,
        string $senderType,
        ?string $actorId,
        string $message
    ): ChatMessage {
        $chatMessage = $conversation
            ->messages()
            ->create([
                'sender_type' =>
                    $senderType,

                'sender_id' =>
                    $senderType === ChatMessage::SENDER_CUSTOMER
                        ? $actorId
                        : null,

                'admin_id' =>
                    $senderType === ChatMessage::SENDER_STAFF
                        ? $actorId
                        : null,

                'message' =>
                    $message,

                'message_type' =>
                    'text',

                'is_read' =>
                    false,
            ]);

        $conversation->forceFill([
            'last_message_at' =>
                now(),
        ])->save();

        return $chatMessage;
    }
}