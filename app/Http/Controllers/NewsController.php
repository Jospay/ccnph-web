<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

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

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('p.PostTitle', 'LIKE', "%{$search}%")
                    ->orWhere('c.CategoryName', 'LIKE', "%{$search}%");
            });
        }

        if (! empty($category) && $category != 'All') {
            $query->where('c.CategoryName', $category);
        }

        $news = $query
            ->orderBy('p.PostingDate', 'DESC')
            ->paginate($limit);

        $categories = DB::connection('news_mysql')
            ->table('tblcategory')
            ->pluck('CategoryName')
            ->unique()
            ->values();

        return Inertia::render('News/NewsMedia', [
            'news' => $news->items(),

            'categories' => [
                'All',
                ...$categories,
            ],

            'pagination' => [
                'current_page' => $news->currentPage(),
                'last_page' => $news->lastPage(),
                'total' => $news->total(),
            ],
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
            ->where('p.Is_Active', 1)
            ->first();

        if (! $news) {
            abort(404);
        }

        $otherNews = DB::connection('news_mysql')
            ->table('tblposts as p')
            ->join('tblcategory as c', 'p.CategoryId', '=', 'c.id')
            ->select(
                'p.id',
                'p.PostTitle',
                'p.PostImage',
                'p.PostingDate',
                'c.CategoryName'
            )
            ->where('p.Is_Active', 1)
            ->where('p.id', '!=', $id) 
            
            ->orderBy('p.PostingDate', 'DESC')
            ->limit(5)
            ->get();

        return Inertia::render('News/NewsDetails', [
            'news' => $news,
            'otherNews' => $otherNews,
        ]);
    }
}