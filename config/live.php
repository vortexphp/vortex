<?php

declare(strict_types=1);

/**
 * Server-driven Live components ({@see \Vortex\Live\LiveHtml}). Only listed classes may be mounted or updated.
 *
 * @var list<class-string> $components
 */
return [
    'components' => [
        \Vortex\vortex\app\Components\Live\Counter::class,
    ],
];
