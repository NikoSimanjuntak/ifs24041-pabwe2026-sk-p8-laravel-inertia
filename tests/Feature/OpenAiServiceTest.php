<?php

namespace Tests\Feature;

use App\Features\Ai\Services\OpenAiPostGeneratorService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiServiceTest extends TestCase
{
    private function fakeContent(mixed $content, int $status = 200): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['choices' => [['message' => ['content' => is_string($content) ? $content : json_encode($content)]]]], $status)]);
    }

    public function test_generates_items_and_limits_to_quantity(): void
    {
        $this->fakeContent(['articles' => [
            ['title' => 'Satu', 'category' => 'Bisnis', 'excerpt' => 'e1', 'content' => 'c1'],
            ['title' => 'Dua', 'category' => 'Aneh', 'excerpt' => 'e2', 'content' => 'c2'],
            'bukan-array',
            ['title' => 'Tiga', 'category' => 'Umum', 'excerpt' => 'e3', 'content' => 'c3'],
        ]]);

        $items = (new OpenAiPostGeneratorService)->generate('Topik', 3, 'Opini');

        $this->assertCount(2, $items);
        $this->assertSame('Bisnis', $items[0]->category);
        $this->assertSame('Umum', $items[1]->category);
        Http::assertSent(fn ($r) => str_contains($r['messages'][1]['content'], 'kategori Opini') && $r['model'] === config('openai.model'));
    }

    public function test_throws_when_api_key_is_missing(): void
    {
        config(['openai.api_key' => null]);

        $this->expectException(RuntimeException::class);
        (new OpenAiPostGeneratorService)->generate('Topik', 1);
    }

    public function test_throws_when_request_fails(): void
    {
        $this->fakeContent('{}', 500);

        $this->expectExceptionMessage('Gagal menghubungi');
        (new OpenAiPostGeneratorService)->generate('Topik', 1);
    }

    public function test_throws_when_response_has_no_valid_articles(): void
    {
        $this->fakeContent('bukan json');

        $this->expectExceptionMessage('tidak mengembalikan artikel');
        (new OpenAiPostGeneratorService)->generate('Topik', 1);
    }
}
