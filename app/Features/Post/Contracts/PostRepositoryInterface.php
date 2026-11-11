<?php

namespace App\Features\Post\Contracts;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?Post;

    public function paginateForUser(User $user, ?string $search, int $perPage = 10): LengthAwarePaginator;

    public function paginateAll(?string $search, int $perPage = 10): LengthAwarePaginator;

    public function allForUser(User $user): Collection;

    public function all(): Collection;

    public function create(array $data): Post;

    public function update(Post $post, array $data): Post;

    public function delete(Post $post): void;

    public function slugExists(string $slug): bool;

    /** @return array{total: int, published: int, draft: int} */
    public function statsForUser(User $user): array;
}
