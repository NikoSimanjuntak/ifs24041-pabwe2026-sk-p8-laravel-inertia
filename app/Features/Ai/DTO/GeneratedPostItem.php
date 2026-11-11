<?php

namespace App\Features\Ai\DTO;

use App\Features\Post\Enums\PostCategory;

final class GeneratedPostItem
{
    public function __construct(
        public readonly string $title,
        public readonly string $category,
        public readonly string $excerpt,
        public readonly string $content,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data, ?string $defaultCategory = null): self
    {
        $category = PostCategory::tryFrom((string) ($data['category'] ?? $defaultCategory))?->value
            ?? PostCategory::Umum->value;

        return new self(
            mb_substr(trim((string) ($data['title'] ?? 'Tanpa Judul')), 0, 255),
            $category,
            trim((string) ($data['excerpt'] ?? '')),
            trim((string) ($data['content'] ?? '')),
        );
    }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'category' => $this->category,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
        ];
    }
}
