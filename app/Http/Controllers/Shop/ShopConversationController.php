<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\ShopConversation;
use App\Services\Shop\ShopConversationNotifier;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShopConversationController extends Controller
{
    public function __construct(private readonly ShopConversationNotifier $notifier)
    {
    }

    public function index(Request $request): Response
    {
        $conversations = ShopConversation::query()
            ->where('seller_id', $request->user()->id)
            ->with(['pinnedProduct', 'buyer', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->latest('updated_at')
            ->paginate(20);

        return Inertia::render('seller/conversations/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(ShopConversation $conversation, Request $request): Response
    {
        Gate::authorize('view', $conversation);

        $conversation->load([
            'messages.sender',
            'messages.attachments',
            'messages.product',
            'pinnedProduct',
            'buyer',
            'seller',
        ]);

        return Inertia::render('seller/conversations/Show', [
            'conversation' => $conversation,
        ]);
    }

    public function storeMessage(ShopConversation $conversation, Request $request): RedirectResponse
    {
        Gate::authorize('reply', $conversation);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:5000', 'required_without:attachments'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'product_id' => $conversation->pinned_product_id,
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

        $this->notifier->notifyOther($conversation, $message, $request->user()->id);

        return back();
    }
}
