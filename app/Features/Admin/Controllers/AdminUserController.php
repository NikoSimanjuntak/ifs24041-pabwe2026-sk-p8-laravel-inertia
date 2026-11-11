<?php

namespace App\Features\Admin\Controllers;

use App\Features\Admin\Services\AdminUserService;
use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Services\PostExcelService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $service) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');

        return Inertia::render('Admin/UserList', [
            'users' => $this->service->paginateUsers($search)->through(fn (User $u) => [
                'id' => $u->id, 'name' => $u->name, 'username' => $u->username,
                'email' => $u->email, 'role' => $u->role, 'posts_count' => $u->posts_count,
            ]),
            'filters' => ['search' => $search],
        ]);
    }

    public function posts(Request $request, User $user): Response
    {
        $search = $request->query('search');

        return Inertia::render('Admin/UserPostList', [
            'owner' => $user->only('id', 'name', 'username'),
            'posts' => $this->service->paginatePostsOf($user, $search)->through(fn ($p) => $p->toSummary()),
            'filters' => ['search' => $search],
        ]);
    }

    public function export(User $user, PostRepositoryInterface $posts, PostExcelService $excel): StreamedResponse
    {
        return $excel->export($posts->allForUser($user), "artikel-{$user->username}.xlsx");
    }
}
