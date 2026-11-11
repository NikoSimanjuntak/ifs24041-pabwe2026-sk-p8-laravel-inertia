<?php

namespace App\Features\Post\Models;

use App\Models\User;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'slug', 'category', 'excerpt', 'content',
        'cover_image_path', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_image_path ? asset('storage/'.$this->cover_image_path) : null;
    }

    /** @return array<string, mixed> */
    public function toSummary(bool $withContent = false): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'cover_url' => $this->coverUrl(),
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->format('d M Y'),
            'created_at' => $this->created_at?->format('d M Y'),
            'author' => $this->user?->name,
            'user_id' => $this->user_id,
        ];

        if ($withContent) {
            $data['content'] = $this->content;
        }

        return $data;
    }
}
