<?php

namespace Tests\Unit;

use App\Features\Ai\DTO\GeneratedPostItem;
use PHPUnit\Framework\TestCase;

class GeneratedPostItemTest extends TestCase
{
    public function test_from_array_normalizes_values(): void
    {
        $item = GeneratedPostItem::fromArray(['title' => '  Halo  ', 'category' => 'Opini', 'excerpt' => ' e ', 'content' => ' c ']);

        $this->assertSame(['title' => 'Halo', 'category' => 'Opini', 'excerpt' => 'e', 'content' => 'c'], $item->toArray());
    }

    public function test_from_array_uses_defaults(): void
    {
        $this->assertSame('Bisnis', GeneratedPostItem::fromArray([], 'Bisnis')->category);
        $this->assertSame('Umum', GeneratedPostItem::fromArray(['category' => 'Ngawur'])->category);
        $this->assertSame('Tanpa Judul', GeneratedPostItem::fromArray([])->title);
    }
}
