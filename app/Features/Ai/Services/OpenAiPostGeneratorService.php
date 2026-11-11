<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public function generate(string $topic, int $quantity, ?string $category = null): array
    {
        $key = config('openai.api_key');
        if (! $key) {
            throw new RuntimeException('OPENAI_API_KEY belum diatur pada berkas .env.');
        }

        $categories = implode(', ', PostCategory::values());
        $system = 'Kamu adalah penulis blog profesional berbahasa Indonesia. Balas HANYA dengan JSON valid '
            .'berbentuk {"articles":[{"title":"","category":"","excerpt":"","content":""}]}. '
            ."Nilai category harus salah satu dari: {$categories}. excerpt berisi 1-2 kalimat. content berformat Markdown.";

        $response = Http::withToken($key)->timeout(90)->post(rtrim(config('openai.base_url'), '/').'/chat/completions', [
            'model' => config('openai.model'),
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => "Buat {$quantity} artikel blog dengan topik: {$topic}."
                    .($category ? " Gunakan kategori {$category}." : '')],
            ],
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Gagal menghubungi layanan OpenAI. Periksa API key dan kuota Anda.');
        }

        $decoded = json_decode((string) $response->json('choices.0.message.content'), true);
        $articles = is_array($decoded) ? ($decoded['articles'] ?? []) : [];

        $items = [];
        foreach (array_slice($articles, 0, $quantity) as $article) {
            if (is_array($article)) {
                $items[] = GeneratedPostItem::fromArray($article, $category);
            }
        }

        if ($items === []) {
            throw new RuntimeException('OpenAI tidak mengembalikan artikel yang valid. Silakan coba lagi.');
        }

        return $items;
    }
}
