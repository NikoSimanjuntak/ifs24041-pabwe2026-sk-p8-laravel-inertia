<?php

namespace App\Features\Post\Repositories;

use App\Features\Post\Contracts\PostRepositoryInterface;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PostRepository implements PostRepositoryInterface
{
    public function paginatePublished(?string $search, ?string $category, int $perPage = 9): LengthAwarePaginator
    {
        return $this->search(Post::query()->with('user:id,name')->where('is_published', true), $search)
            ->when($category, fn (Builder $q) => $q->where('category', $category))
            ->orderByDesc('published_at')->orderByDesc('id')
            ->paginate($perPage)->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Post
    {
        return Post::query()->with('user:id,name')->where('is_published', true)->where('slug', $slug)->first();
    }

    public function paginateForUser(User $user, ?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->search(Post::query()->with('user:id,name')->where('user_id', $user->id), $search)
            ->latest('id')->paginate($perPage)->withQueryString();
    }

    public function paginateAll(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return $this->search(Post::query()->with('user:id,name'), $search)
            ->latest('id')->paginate($perPage)->withQueryString();
    }

    public function allForUser(User $user): Collection
    {
        return Post::query()->with('user:id,name')->where('user_id', $user->id)->orderBy('id')->get();
    }

    public function all(): Collection
    {
        return Post::query()->with('user:id,name')->orderBy('id')->get();
    }

    public function create(array $data): Post
    {
        return Post::create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post;
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }

    public function slugExists(string $slug): bool
    {
        return Post::query()->where('slug', $slug)->exists();
    }

    public function statsForUser(User $user): array
    {
        $total = Post::query()->where('user_id', $user->id)->count();
        $published = Post::query()->where('user_id', $user->id)->where('is_published', true)->count();

        return ['total' => $total, 'published' => $published, 'draft' => $total - $published];
    }

    private function search(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function (Builder $q) use ($search) {
            $term = '%'.mb_strtolower($search).'%';
            $q->where(fn (Builder $w) => $w
                ->whereRaw('LOWER(title) LIKE ?', [$term])
                ->orWhereRaw('LOWER(content) LIKE ?', [$term]));
        });
    }
}
