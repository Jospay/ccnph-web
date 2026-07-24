<?php

namespace App\Http\Controllers\API\Store;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index(Request $request, $productId)
    {
        // 1. Build base query directly on the reviews table/model
        $reviewsQuery = Review::query()
            ->where('product_id', $productId)
            ->with(['user:id,name,avatar', 'images'])
            ->latest();

        if ($request->filled('rating') && $request->rating !== 'all') {
            $reviewsQuery->where('rating', (int) $request->rating);
        }

        $reviews = $reviewsQuery->paginate(10);

        // 2. Aggregate stats
        $rawStats = Review::where('product_id', $productId)
            ->selectRaw('
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as star_5,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as star_4,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as star_3,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as star_2,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as star_1
            ')
            ->first();

        $stats = [
            'average_rating' => round((float) ($rawStats->average_rating ?? 0), 1),
            'total_reviews' => (int) ($rawStats->total_reviews ?? 0),
            'breakdown' => [
                5 => (int) ($rawStats->star_5 ?? 0),
                4 => (int) ($rawStats->star_4 ?? 0),
                3 => (int) ($rawStats->star_3 ?? 0),
                2 => (int) ($rawStats->star_2 ?? 0),
                1 => (int) ($rawStats->star_1 ?? 0),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats,
                'reviews' => collect($reviews->items())->map(fn ($review) => [
                    'id' => $review->id,
                    'rating' => (int) $review->rating,
                    'comment' => $review->review ?? $review->comment ?? null,
                    'created_at' => $review->created_at ? $review->created_at->format('M d, Y') : '',
                    'user_name' => $review->is_anonymous
                        ? 'Anonymous'
                        : ($review->user->name ?? 'Customer'),
                    'user_avatar' => ($review->is_anonymous || ! $review->user?->avatar)
                        ? null
                        : asset('storage/'.$review->user->avatar),
                    'video_url' => $review->video ? asset('storage/'.$review->video) : null,
                    'is_anonymous' => (bool) $review->is_anonymous,
                    'images' => $review->images->map(
                        fn ($img) => asset('storage/'.$img->image)
                    )->values()->all(),
                ]),
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'has_more' => $reviews->hasMorePages(),
                    'total' => $reviews->total(),
                ],
            ],
        ]);
    }
}
