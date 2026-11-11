<?php

namespace Tests\Unit;

use App\Features\Post\Enums\PostCategory;
use PHPUnit\Framework\TestCase;

class PostCategoryTest extends TestCase
{
    public function test_values_and_options(): void
    {
        $this->assertSame(['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'], PostCategory::values());
        $this->assertCount(6, PostCategory::options());
        $this->assertSame(['value' => 'Gaya Hidup', 'label' => 'Gaya Hidup & Produktivitas'], PostCategory::options()[2]);
    }

    public function test_every_case_has_a_label(): void
    {
        $labels = array_map(fn (PostCategory $c) => $c->label(), PostCategory::cases());

        $this->assertSame([
            'Teknologi & Koding', 'Pendidikan & Tutorial', 'Gaya Hidup & Produktivitas',
            'Bisnis & Karir', 'Opini & Pemikiran', 'Umum',
        ], $labels);
    }
}
