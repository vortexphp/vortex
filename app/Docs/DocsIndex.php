<?php

declare(strict_types=1);

namespace App\Docs;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Discovers Markdown under docs/, resolves safe paths, and builds the sidebar from docs/menu.php.
 */
final class DocsIndex
{
    /** @var array<string, array{slug: string, title: string, href: string}>|null */
    private ?array $pagesCache = null;

    public function __construct(
        private readonly string $docsRoot,
    ) {
    }

    /**
     * @return list<array{slug: string, title: string, href: string}>
     */
    public function listPages(): array
    {
        $map = $this->pagesBySlug();

        return array_values($map);
    }

    /**
     * @return array{sections: list<array{title: string, items: list<array{slug: string, title: string, href: string}>}>}
     */
    public function navigation(): array
    {
        $map = $this->pagesBySlug();
        if ($map === []) {
            return ['sections' => []];
        }

        $menuFile = $this->docsRoot . '/menu.php';
        /** @var mixed $rawMenu */
        $rawMenu = is_file($menuFile) ? require $menuFile : null;
        if (! is_array($rawMenu) || $rawMenu === []) {
            return [
                'sections' => [
                    $this->oneSection(\trans('docs.menu.all_pages'), array_values($map)),
                ],
            ];
        }

        $listed = [];
        $sections = [];

        foreach ($rawMenu as $block) {
            if (! is_array($block) || ! isset($block['items']) || ! is_array($block['items'])) {
                continue;
            }

            $title = $this->sectionTitle($block);
            $items = [];
            foreach ($block['items'] as $slug) {
                if (! is_string($slug)) {
                    continue;
                }
                $slug = trim($slug, '/');
                if ($slug === '' || ! isset($map[$slug])) {
                    continue;
                }
                $items[] = $map[$slug];
                $listed[$slug] = true;
            }

            if ($items !== []) {
                $sections[] = ['title' => $title, 'items' => $items];
            }
        }

        $orphans = [];
        foreach ($map as $slug => $page) {
            if (! isset($listed[$slug])) {
                $orphans[] = $page;
            }
        }

        if ($orphans !== []) {
            usort($orphans, static fn (array $a, array $b): int => strcmp($a['title'], $b['title']));
            $sections[] = $this->oneSection(\trans('docs.menu.other'), $orphans);
        }

        return ['sections' => $sections];
    }

    /**
     * Sidebar order, flattened (for prev/next links).
     *
     * @return list<array{slug: string, title: string, href: string}>
     */
    public function pagesInMenuOrder(): array
    {
        $flat = [];
        foreach ($this->navigation()['sections'] as $section) {
            foreach ($section['items'] as $item) {
                $flat[] = $item;
            }
        }

        return $flat;
    }

    public function resolveFile(string $slug): ?string
    {
        $slug = trim($slug, '/');
        if ($slug === '' || str_contains($slug, '..')) {
            return null;
        }

        $candidate = $this->docsRoot . '/' . str_replace('/', DIRECTORY_SEPARATOR, $slug) . '.md';
        $realRoot = realpath($this->docsRoot);
        if ($realRoot === false) {
            return null;
        }

        $realFile = realpath($candidate);
        if ($realFile === false || ! is_file($realFile)) {
            return null;
        }

        $rootWithSep = $realRoot . DIRECTORY_SEPARATOR;
        if (! str_starts_with($realFile, $rootWithSep) && $realFile !== $realRoot) {
            return null;
        }

        if (! str_ends_with(strtolower($realFile), '.md')) {
            return null;
        }

        return $realFile;
    }

    public function documentTitle(string $slug, string $markdown): string
    {
        $h = self::firstMarkdownHeading($markdown);
        if ($h !== null) {
            return $h;
        }

        return $this->titleForSlug($slug);
    }

    public static function hrefForSlug(string $slug): string
    {
        $slug = trim($slug, '/');
        if ($slug === '') {
            return '/docs';
        }

        return '/docs/' . implode('/', array_map(rawurlencode(...), explode('/', $slug)));
    }

    public static function firstMarkdownHeading(string $markdown): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $markdown, $m) === 1) {
            return trim($m[1], " \t#");
        }

        return null;
    }

    /**
     * @return array<string, array{slug: string, title: string, href: string}>
     */
    private function pagesBySlug(): array
    {
        if ($this->pagesCache !== null) {
            return $this->pagesCache;
        }

        if (! is_dir($this->docsRoot)) {
            return $this->pagesCache = [];
        }

        $out = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->docsRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || ! str_ends_with(strtolower($file->getFilename()), '.md')) {
                continue;
            }

            $abs = $file->getPathname();
            $rel = substr($abs, strlen($this->docsRoot) + 1);
            $rel = str_replace('\\', '/', $rel);
            $slug = (string) preg_replace('#\.md$#i', '', $rel);
            if ($slug === '') {
                continue;
            }

            $heading = $this->firstHeadingFromFile($abs);
            $out[$slug] = [
                'slug' => $slug,
                'title' => $heading ?? $this->titleForSlug($slug),
                'href' => self::hrefForSlug($slug),
            ];
        }

        return $this->pagesCache = $out;
    }

    private function firstHeadingFromFile(string $absolutePath): ?string
    {
        $raw = @file_get_contents($absolutePath, false, null, 0, 12_000);
        if ($raw === false) {
            return null;
        }

        return self::firstMarkdownHeading($raw);
    }

    /**
     * @param list<array{slug: string, title: string, href: string}> $items
     *
     * @return array{title: string, items: list<array{slug: string, title: string, href: string}>}
     */
    private function oneSection(string $title, array $items): array
    {
        return ['title' => $title, 'items' => $items];
    }

    /**
     * @param array<string, mixed> $block
     */
    private function sectionTitle(array $block): string
    {
        if (isset($block['title_key']) && is_string($block['title_key']) && $block['title_key'] !== '') {
            return \trans($block['title_key']);
        }

        if (isset($block['title']) && is_string($block['title']) && $block['title'] !== '') {
            return $block['title'];
        }

        return \trans('docs.menu.section');
    }

    private function titleForSlug(string $slug): string
    {
        $base = basename(str_replace('\\', '/', $slug));

        return str_replace(['-', '_'], ' ', $base);
    }
}
