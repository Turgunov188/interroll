<?php

namespace Tests\Unit;

use App\Enums\Locale;
use PHPUnit\Framework\TestCase;

class LocaleTest extends TestCase
{
    public function test_main_language_has_no_prefix(): void
    {
        $this->assertSame('/', Locale::Ru->path('/'));
        $this->assertSame('/catalog', Locale::Ru->path('/catalog'));
    }

    public function test_other_languages_are_prefixed(): void
    {
        $this->assertSame('/uz', Locale::Uz->path('/'));
        $this->assertSame('/en/blog/shore-a-hardness', Locale::En->path('/blog/shore-a-hardness'));
        $this->assertSame('/tr/catalog', Locale::Tr->path('/catalog'));
    }
}
