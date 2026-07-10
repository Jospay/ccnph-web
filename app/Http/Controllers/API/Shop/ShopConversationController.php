<?php

namespace App\Http\Controllers\API\Shop;

use App\Http\Controllers\Controller;
use App\Models\ShopConversation;
use App\Services\Shop\ShopConversationNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ShopConversationController extends Controller
{
    public function __construct(private readonly ShopConversationNotifier $notifier)
    {
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'seller_id' => ['required', 'integer', 'exists:users,id', 'different:buyer_id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
        ]);

        $conversation = ShopConversation::findOrStartBetween(
            buyerId: $request->user()->id,
            sellerId: $validated['seller_id'],
            productId: $validated['product_id'] ?? null,
        );

        return $conversation->load(['pinnedProduct', 'buyer', 'seller']);
    }

    public function index(Request $request)
    {
        return ShopConversation::query()
            ->where('buyer_id', $request->user()->id)
            ->with(['pinnedProduct', 'seller', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->paginate(20);
    }

    public function show(ShopConversation $conversation, Request $request)
    {
        Gate::authorize('view', $conversation);

        return $conversation->load([
            'messages.sender',
            'messages.attachments',
            'messages.product',
            'pinnedProduct',
            'buyer',
            'seller',
        ]);
    }

    public function storeMessage(ShopConversation $conversation, Request $request)
    {
        Gate::authorize('reply', $conversation);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachments'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'product_id' => $validated['product_id'] ?? $conversation->pinned_product_id,
            'body' => $validated['body'] ?? null,
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

        if (!empty($validated['product_id']) && $validated['product_id'] !== $conversation->pinned_product_id) {
            $conversation->update(['pinned_product_id' => $validated['product_id']]);
        }

        $this->notifier->notifyOther($conversation, $message, $request->user()->id);

        return $message->load(['sender', 'attachments', 'product']);
    }
}
