<?php

declare(strict_types=1);

namespace PowerCode\tests;

use App\Docs\DocOutline;
use App\Docs\MarkdownRenderer;
use PHPUnit\Framework\TestCase;

final class DocOutlineTest extends TestCase
{
    public function testOutlineMatchesRenderedHeadingIds(): void
    {
        $md = "## Section A\n\nPara.\n\n### Nested B\n";
        $html = (new MarkdownRenderer())->toHtml($md);
        $outline = DocOutline::fromArticleHtml($html);

        self::assertNotEmpty($outline);
        self::assertSame(2, count($outline));
        self::assertSame(2, $outline[0]['level']);
        self::assertSame(3, $outline[1]['level']);
        self::assertNotSame('', $outline[0]['id']);
        self::assertStringContainsString('id="' . $outline[0]['id'] . '"', $html);
    }
}
