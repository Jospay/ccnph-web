<?php

namespace App\Http\Controllers\API\Notification;

use App\Http\Controllers\Controller;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the user's notifications (GeneralNotification only).
     */
    public function index(Request $request)
    {
        $query = $request->user()
            ->notifications()
            ->where('type', GeneralNotification::class);

        // Clone before mapping so we can still count unread separately
        $unreadCount = (clone $query)->whereNull('read_at')->count();

        $notifications = $query
            ->latest()
            ->get()
            ->map(function ($notification) {
                $data = $notification->data;

                return [
                    'id' => $notification->id,
                    'type' => $data['type'] ?? 'general',
                    'title' => $data['title'] ?? 'Notification',
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
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('type', GeneralNotification::class)
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    /**
     * Mark all unread notifications as read (GeneralNotification only).
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->where('type', GeneralNotification::class)
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.',
        ]);
    }
}
