<?php

namespace App\Http\Controllers\API\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $category = $request->category;
        $limit = $request->limit ?? 10;

        $query = DB::connection('news_mysql')
            ->table('tblposts as p')
            ->join('tblcategory as c', 'p.CategoryId', '=', 'c.id')
            ->select(
                'p.id',
                'p.PostTitle',
                'p.PostImage',
                'p.PostUrl',
                'p.PostingDate',
                'c.CategoryName'
            )
            ->where('p.Is_Active', 1);

        // ================= SEARCH =================
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('p.PostTitle', 'LIKE', "%{$search}%")
                  ->orWhere('c.CategoryName', 'LIKE', "%{$search}%");
            });
        }

        // ================= CATEGORY =================
        if (!empty($category)) {
            $query->where('c.CategoryName', $category);
        }

        $news = $query
            ->orderBy('p.PostingDate', 'desc')
            ->paginate($limit);

        return response()->json([
            'status' => true,
            'data' => $news->items(),
            'meta' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'total' => $news->total(),
            ]
        ]);
    }

    public function show($id)
    {
        $news = DB::connection('news_mysql')
            ->table('tblposts as p')
            ->join('tblcategory as c', 'p.CategoryId', '=', 'c.id')
            ->select(
                'p.id',
                'p.PostTitle',
                'p.PostDetails',
                'p.PostImage',
                'p.PostUrl',
                'p.PostingDate',
                'c.CategoryName'
            )
            ->where('p.id', $id)
            ->first();

        return response()->json([
            'status' => true,
            'data' => $news
        ]);
    }
}
