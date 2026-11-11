<?php

namespace App\Features\Admin\Services;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class AdminUserService
{
    public function __construct(private readonly PostRepositoryInterface $posts) {}

    public function paginateUsers(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->withCount('posts')
            ->when($search, function (Builder $q) use ($search) {
                $term = '%'.mb_strtolower($search).'%';
                $q->where(fn (Builder $w) => $w
                    ->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]));
            })
            ->orderBy('id')
            ->paginate($perPage)->withQueryString();
    }

    public function paginatePostsOf(User $user, ?string $search): LengthAwarePaginator
    {
        return $this->posts->paginateForUser($user, $search);
    }

    public function paginateAllPosts(?string $search): LengthAwarePaginator
    {
        return $this->posts->paginateAll($search);
    }
}
