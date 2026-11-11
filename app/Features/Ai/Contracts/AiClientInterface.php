<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedPostItem;

interface AiClientInterface
{
    /** @return array<int, GeneratedPostItem> */
    public function generate(string $topic, int $quantity, ?string $category = null): array;
}
