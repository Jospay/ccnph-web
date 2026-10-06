<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class NewsController extends Controller
{
    /**
     * Create the HTTP client for the external News API.
     */
    private function client()
    {
        return Http::withHeaders([
            'X-API-KEY' => env('NEWS_API_KEY'),
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Display the News & Media page.
     */
    public function index(Request $request)
    {
        /*
         * The external API already handles:
         * - Category ID
         * - Search
         * - Pagination
         * - Total count
         *
         * Do not add category filtering here.
         */
        $limit = (int) ($request->limit ?? 10);
        $page = (int) ($request->page ?? 1);

        $limit = max(1, min(100, $limit));
        $page = max(1, $page);

        /*
         * Fetch the current page of news.
         */
        $response = $this->client()->get(env('NEWS_API_URL'), [
            'action' => 'index',
            'search' => $request->search,
            'limit' => $limit,
            'page' => $page,
        ]);

        if ($response->failed()) {
            return Inertia::render('News/NewsMedia', [
                'news' => [],
                'otherNews' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => $limit,
                ],
                'search' => $request->search,
            ]);
        }

        $responseData = $response->json();

        $newsList = $responseData['data'] ?? [];

        /*
         * IMPORTANT:
         *
         * Your API returns pagination inside "meta":
         *
         * "meta": {
         *     "current_page": 1,
         *     "last_page": 10,
         *     "total": 100
         * }
         *
         * Therefore we read "meta", not "pagination".
         */
        $meta = $responseData['meta'] ?? [];

        $pagination = [
            'current_page' => (int) ($meta['current_page'] ?? $page),
            'last_page' => (int) ($meta['last_page'] ?? 1),
            'total' => (int) ($meta['total'] ?? count($newsList)),
            'per_page' => $limit,
        ];

        /*
         * Fetch additional articles for the "Other News" section.
         *
         * We request up to 100 articles because the API allows
         * a maximum limit of 100.
         *
         * This does NOT affect the main pagination.
         */
        $otherNewsResponse = $this->client()->get(env('NEWS_API_URL'), [
            'action' => 'index',
            'limit' => 100,
            'page' => 1,
        ]);

        $otherNews = [];

        if (
            $otherNewsResponse->successful() &&
            isset($otherNewsResponse->json()['data'])
        ) {
            /*
             * Get IDs from the current page.
             *
             * These articles should not be repeated
             * inside "Other News".
             */
            $currentNewsIds = collect($newsList)
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->all();

            $otherNews = collect($otherNewsResponse->json()['data'])
                ->reject(function ($item) use ($currentNewsIds) {
                    return in_array(
                        (string) ($item['id'] ?? ''),
                        $currentNewsIds,
                        true
                    );
                })
                ->take(5)
                ->values()
                ->all();
        }

        return Inertia::render('News/NewsMedia', [
            'news' => $newsList,
            'otherNews' => $otherNews,
            'pagination' => $pagination,
            'search' => $request->search,
        ]);
    }

    /**
     * Display a specific news article.
     */
    public function show($id)
    {
        /*
         * Fetch the selected news article.
         */
        $response = $this->client()->get(env('NEWS_API_URL'), [
            'action' => 'show',
            'id' => $id,
        ]);

        if (
            $response->failed() ||
            empty($response->json()['data'])
        ) {
            abort(404, 'News item not found');
        }

        $news = $response->json()['data'];

        // Generate the original NPO article URL.
        $news['npo_url'] = 'https://newsphilippinesonline.com/news-details.php?nid='
            .base64_encode((string) $news['id']);

        /*
         * Fetch other news for recommendations.
         */
        $otherNewsResponse = $this->client()->get(env('NEWS_API_URL'), [
            'action' => 'index',
            'limit' => 100,
            'page' => 1,
        ]);

        $otherNews = [];

        if (
            $otherNewsResponse->successful() &&
            isset($otherNewsResponse->json()['data'])
        ) {
            $otherNews = collect($otherNewsResponse->json()['data'])
                ->reject(function ($item) use ($id) {
                    return (string) ($item['id'] ?? '') === (string) $id;
                })
                ->take(5)
                ->values()
                ->all();
        }

        return Inertia::render('News/NewsDetails', [
            'news' => $news,
            'otherNews' => $otherNews,
        ]);
    }
}
