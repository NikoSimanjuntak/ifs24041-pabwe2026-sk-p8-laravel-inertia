<?php

namespace App\Features\Post\Controllers;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicPostController extends Controller
{
    public function __construct(private readonly PostService $service) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $category = $request->query('category');

        return Inertia::render('Public/PostIndex', [
            'posts' => $this->service->publicList($search, $category)->through(fn (Post $p) => $p->toSummary()),
            'filters' => ['search' => $search, 'category' => $category],
            'categories' => PostCategory::options(),
        ]);
    }

    public function show(string $slug): Response
    {
        $post = $this->service->publicDetail($slug) ?? abort(404);

        return Inertia::render('Public/PostShow', [
            'post' => $post->toSummary(true),
        ]);
    }
}
