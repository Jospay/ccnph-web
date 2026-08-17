<?php

namespace App\Http\Controllers\API\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NewsController extends Controller
{
    private function client()
    {
        return Http::withHeaders([
            'X-API-KEY' => env('NEWS_API_KEY'),
            'Accept' => 'application/json',
        ]);
    }

    public function index(Request $request)
    {
        $response = $this->client()->get(env('NEWS_API_URL'), [
            'action' => 'index',
            'search' => $request->search,
            'category' => $request->category,
            'limit' => $request->limit ?? 10,
            'page' => $request->page ?? 1,
        ]);

        if ($response->failed()) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to fetch news from server',
            ], $response->status());
        }

        return response()->json($response->json());
    }

    public function show($id)
    {
        $response = $this->client()->get(env('NEWS_API_URL'), [
            'action' => 'show',
            'id' => $id,
        ]);

        if ($response->failed()) {
            return response()->json([
                'status' => false,
                'message' => 'News item not found or server error',
            ], $response->status());
        }

        return response()->json($response->json());
    }
}
