<?php

namespace App\Features\Post\Controllers;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Services\PostExcelService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPostController extends Controller
{
    public function index(Request $request, AdminUserService $service): Response
    {
        $search = $request->query('search');

        return Inertia::render('Admin/PostList', [
            'posts' => $service->paginateAllPosts($search)->through(fn ($p) => $p->toSummary()),
            'filters' => ['search' => $search],
        ]);
    }

    public function export(PostRepositoryInterface $posts, PostExcelService $excel): StreamedResponse
    {
        return $excel->export($posts->all(), 'semua-artikel.xlsx', true);
    }
}
