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
    public function __construct(private readonly ShopConversationNotifier $notifier)
    {
    }

    /**
     * List the authenticated customer's conversations across shops.
     */
    public function index(Request $request): JsonResponse
    {
        $conversations = ShopConversation::query()
            ->where('user_id', $request->user()->id)
            ->with(['shop', 'pinnable', 'latestMessage'])
            ->latest('updated_at')
            ->paginate(20);

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
     * View a single conversation and its messages.
     */
    public function show(ShopConversation $conversation, Request $request): JsonResponse
    {
        Gate::authorize('view', $conversation);

        $conversation->load([
            'messages.sender',
            'messages.attachments',
            'messages.context',
            'pinnable',
            'shop',
        ]);

        $conversation->update(['user_read_at' => now()]);

        return response()->json($conversation);
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
}