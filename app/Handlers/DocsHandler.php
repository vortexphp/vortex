<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Docs\DocOutline;
use App\Docs\DocsIndex;
use App\Docs\MarkdownRenderer;
use Vortex\Config\Repository;
use Vortex\Http\ErrorRenderer;
use Vortex\Http\Response;
use Vortex\View\View;

final class DocsHandler
{
    public function __construct(
        private readonly ErrorRenderer $errors,
        private readonly DocsIndex $docs,
        private readonly MarkdownRenderer $markdown,
    ) {
    }

    public function index(): Response
    {
        if (! $this->previewEnabled()) {
            return $this->errors->notFound();
        }

        $nav = $this->docs->navigation();
        $hasPages = false;
        foreach ($nav['sections'] as $section) {
            if ($section['items'] !== []) {
                $hasPages = true;
                break;
            }
        }

        return View::html('docs.index', [
            'title' => \trans('docs.index_title'),
            'navSections' => $nav['sections'],
            'currentSlug' => null,
            'hasPages' => $hasPages,
            'pageCount' => count($this->docs->listPages()),
        ]);
    }

    public function show(string $path): Response
    {
        if (! $this->previewEnabled()) {
            return $this->errors->notFound();
        }

        $slug = trim(rawurldecode($path), '/');
        $file = $this->docs->resolveFile($slug);
        if ($file === null) {
            return $this->errors->notFound();
        }

        $raw = file_get_contents($file);
        if ($raw === false) {
            return $this->errors->notFound();
        }

        $docTitle = $this->docs->documentTitle($slug, $raw);
        $html = $this->markdown->toHtml($raw);
        $nav = $this->docs->navigation();
        $outline = DocOutline::fromArticleHtml($html);
        [$prevPage, $nextPage] = $this->neighborPages($slug);

        return View::html('docs.show', [
            'title' => $docTitle,
            'slug' => $slug,
            'contentHtml' => $html,
            'breadcrumbs' => $this->breadcrumbsForSlug($slug, $docTitle),
            'navSections' => $nav['sections'],
            'currentSlug' => $slug,
            'outline' => $outline,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
        ]);
    }

    /**
     * @return list<array{label: string, href: string|null}>
     */
    private function breadcrumbsForSlug(string $slug, string $currentDocumentTitle): array
    {
        $parts = explode('/', str_replace('\\', '/', $slug));
        $out = [
            ['label' => \trans('docs.breadcrumb_index'), 'href' => '/docs'],
        ];
        $path = '';
        $n = count($parts);
        foreach ($parts as $i => $segment) {
            $path = $path === '' ? $segment : $path . '/' . $segment;
            $isLast = $i === $n - 1;
            $out[] = [
                'label' => $isLast ? $currentDocumentTitle : str_replace(['-', '_'], ' ', $segment),
                'href' => $isLast ? null : DocsIndex::hrefForSlug($path),
            ];
        }

        return $out;
    }

    /**
     * @return array{0: array{slug: string, title: string, href: string}|null, 1: array{slug: string, title: string, href: string}|null}
     */
    private function neighborPages(string $slug): array
    {
        $flat = $this->docs->pagesInMenuOrder();
        foreach ($flat as $i => $item) {
            if ($item['slug'] !== $slug) {
                continue;
            }

            $prev = $i > 0 ? $flat[$i - 1] : null;
            $next = isset($flat[$i + 1]) ? $flat[$i + 1] : null;

            return [$prev, $next];
        }

        return [null, null];
    }

    private function previewEnabled(): bool
    {
        return (bool) Repository::get('app.debug', false);
    }
}
