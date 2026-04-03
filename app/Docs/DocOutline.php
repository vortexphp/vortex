<?php

declare(strict_types=1);

namespace App\Docs;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Headings with ids (from {@see MarkdownRenderer}) for in-page navigation.
 */
final class DocOutline
{
    /**
     * @return list<array{id: string, level: int, text: string}>
     */
    public static function fromArticleHtml(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $prev = libxml_use_internal_errors(true);
        $wrapped = '<?xml encoding="UTF-8"><div class="doc-outline-root">' . $html . '</div>';
        $dom = new DOMDocument();
        $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query('//h2|//h3|//h4');
        if ($nodes === false) {
            return [];
        }

        $out = [];
        foreach ($nodes as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }
            $id = $node->getAttribute('id');
            if ($id === '') {
                continue;
            }
            $level = (int) substr($node->tagName, 1);
            $text = trim(preg_replace('/\s+/u', ' ', $node->textContent) ?? '');
            if ($text === '') {
                continue;
            }
            $out[] = ['id' => $id, 'level' => $level, 'text' => $text];
        }

        return $out;
    }
}
