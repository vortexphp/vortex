<?php

declare(strict_types=1);

/**
 * Documentation sidebar: section order and page order (slugs without `.md`).
 * Any discovered Markdown file not listed here appears under “Other” at the bottom.
 *
 * Optional keys per section:
 * - `title_key` — translation key (preferred for en/bg)
 * - `title` — literal label if no key
 */
return [
    [
        'title_key' => 'docs.menu.getting_started',
        'items' => [
            'README',
            'PRODUCTION',
        ],
    ],
    [
        'title_key' => 'docs.menu.developer',
        'items' => [
            'developer/README',
            'developer/project-structure',
            'developer/routes',
            'developer/handlers',
            'developer/views',
            'developer/response',
            'developer/validation',
            'developer/middleware',
            'developer/models',
            'developer/auth',
            'developer/database',
            'developer/cache',
            'developer/events',
            'developer/mail',
            'developer/frontend',
            'developer/testing',
            'developer/checklist',
            'developer/docs-site',
            'developer/uploads',
        ],
    ],
    [
        'title_key' => 'docs.menu.framework',
        'items' => [
            'framework/README',
            'framework/bootstrap-and-container',
            'framework/configuration',
            'framework/cache',
            'framework/events',
            'framework/mail',
            'framework/http',
            'framework/views',
            'framework/files-and-uploads',
            'framework/i18n',
            'framework/crypto',
            'framework/support-helpers',
            'framework/console',
            'framework/validation',
            'framework/errors-and-logging',
        ],
    ],
];
