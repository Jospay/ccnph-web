<?php

namespace App\Http\Controllers\Web\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('seller/dashboard/Index');
    }
}
