<?php

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use App\Notifications\StoreNotification;
use Illuminate\Http\Request;

class StoreNotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications (StoreNotification only).
     */
    public function index(Request $request)
    {
        $query = $request->user()
            ->notifications()
            ->where('type', StoreNotification::class);

        $unreadCount = (clone $query)->whereNull('read_at')->count();

        $notifications = $query
            ->latest()
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;

                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'store',
                    'title' => $data['title'] ?? 'Store Notification',
                    'description' => $data['body'] ?? $data['description'] ?? '',
                    'actionType' => $data['action_type'] ?? 'NO_ACTION',
                    'route' => $data['route'] ?? null,
                    'extraData' => array_diff_key($data, array_flip(['type', 'title', 'body', 'action_type', 'route'])),
                    'isRead' => ! is_null($notification->read_at),
                    'timestamp' => $notification->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a specific store notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('type', StoreNotification::class)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all unread store notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->where('type', StoreNotification::class)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}
