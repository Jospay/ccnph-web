<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use App\Models\ShopConversation;
use App\Models\ShopMessage;
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
            ->whereHas('shop', fn ($q) => $q->where('user_id', $request->user()->id))
            ->with(['pinnable', 'user', 'latestMessage'])
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
            'messages.context',
            'pinnable',
            'user',
            'shop',
        ]);

        $conversation->update(['shop_read_at' => now()]);

        return Inertia::render('seller/conversations/Show', [
            'conversation' => $conversation,
        ]);
    }

    public function storeMessage(ShopConversation $conversation, Request $request): RedirectResponse
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

        return back();
    }
}