<?php

namespace App\Http\Controllers\API\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopConversation;
use App\Models\ShopMessage;
use App\Services\Shop\ShopConversationNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ShopConversationController extends Controller
{
    public function __construct(private readonly ShopConversationNotifier $notifier) {}

    /**
     * List the authenticated customer's conversations across shops.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $conversations = ShopConversation::query()
            ->where('user_id', $userId)
            ->with(['shop', 'pinnable', 'latestMessage'])
            ->latest('updated_at')
            ->paginate(20);

        $conversations->getCollection()->each(function ($conversation) use ($userId) {
            $isShopOwner = $conversation->shop->user_id === $userId;

            $lastReadAt = $isShopOwner
                ? $conversation->shop_read_at
                : $conversation->user_read_at;

            $conversation->unread_count = $conversation->messages()
                ->where('sender_id', '!=', $userId)
                ->when($lastReadAt, function ($query) use ($lastReadAt) {
                    $query->where('created_at', '>', $lastReadAt);
                })
                ->count();
        });

        return response()->json($conversations);
    }

    /**
     * Start (or resume) a conversation with a shop, sending the first message
     * and optionally pinning an order or product as context.
     */
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shop_id' => ['required', 'exists:shops,id'],
            'order_id' => ['nullable', 'exists:orders,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'body' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $shop = Shop::findOrFail($validated['shop_id']);

        $conversation = DB::transaction(function () use ($validated, $shop, $request) {
            $conversation = ShopConversation::firstOrCreate([
                'shop_id' => $shop->id,
                'user_id' => $request->user()->id,
            ]);

            $context = null;
            if (! empty($validated['order_id'])) {
                $context = Order::findOrFail($validated['order_id']);
            } elseif (! empty($validated['product_id'])) {
                $context = Product::findOrFail($validated['product_id']);
            }

            if ($context) {
                $conversation->pin($context);
            }

            $message = $conversation->messages()->create([
                'sender_id' => $request->user()->id,
                'sender_type' => ShopMessage::SENDER_USER,
                'body' => $validated['body'],
                'context_type' => $conversation->pinnable_type,
                'context_id' => $conversation->pinnable_id,
            ]);

            foreach ($request->file('attachments', []) as $file) {
                $path = $file->store("shop-conversations/{$conversation->id}/attachments", 'public');

                $message->attachments()->create([
                    'path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ]);
            }

            $conversation->update(['last_message_at' => $message->created_at]);

            $this->notifier->notifyOther($conversation, $message, $request->user()->id);

            return $conversation;
        });

        return response()->json(
            $conversation->fresh(['shop', 'pinnable', 'messages.attachments']),
            201
        );
    }

    /**
     * View a single conversation and its (paginated) messages.
     *
     * Mirrors ConversationController::show — latest() + paginate(15),
     * then reverse the page's collection to oldest-first so the client
     * can build an inverted FlatList the same way it does for the
     * intellectual chat.
     */
    public function show(ShopConversation $conversation, Request $request): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $conversation->update(['user_read_at' => now()]);

        $messages = $conversation->messages()
            ->with(['sender', 'attachments', 'context'])
            ->latest()
            ->paginate(15);

        $messages->setCollection($messages->getCollection()->reverse()->values());

        $messages->getCollection()->each(function ($message) {
            $message->attachments->each(function ($attachment) {
                $attachment->path = $attachment->path ? asset('storage/'.$attachment->path) : null;
            });
        });

        return response()->json([
            'conversation' => $conversation->load(['pinnable', 'shop']),
            'messages' => $messages->items(),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'has_more' => $messages->hasMorePages(),
            ],
        ]);
    }

    /**
     * Reply within an existing conversation.
     */
    public function storeMessage(ShopConversation $conversation, Request $request): JsonResponse
    {
        Gate::authorize('sendMessage', $conversation);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachments'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $senderType = $conversation->shop->user_id === $request->user()->id
            ? ShopMessage::SENDER_SHOP
            : ShopMessage::SENDER_USER;

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'sender_type' => $senderType,
            'body' => $validated['body'] ?? null,
            'context_type' => $conversation->pinnable_type,
            'context_id' => $conversation->pinnable_id,
        ]);

        foreach ($request->file('attachments', []) as $file) {
            $path = $file->store("shop-conversations/{$conversation->id}/attachments", 'public');

            $message->attachments()->create([
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        $conversation->update(['last_message_at' => $message->created_at]);

        $this->notifier->notifyOther($conversation, $message, $request->user()->id);

        return response()->json($message->load('attachments'), 201);
    }

    /**
     * Mark all messages in the shop conversation as read for the authenticated user.
     */
    public function markRead(ShopConversation $conversation, Request $request): JsonResponse
    {
        // Authorize that the user belongs to this conversation or owns the shop
        Gate::authorize('view', $conversation);

        $userId = $request->user()->id;

        // Check if the current user is the owner of the shop
        $isShopOwner = $conversation->shop && $conversation->shop->user_id === $userId;

        if ($isShopOwner) {
            $conversation->update(['shop_read_at' => now()]);
        } else {
            $conversation->update(['user_read_at' => now()]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Conversation marked as read.',
        ]);
    }
}
