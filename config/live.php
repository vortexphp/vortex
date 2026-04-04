<?php

declare(strict_types=1);

/**
 * Server-driven Live components ({@see \Vortex\Live\LiveHtml}). Only listed classes may be mounted or updated.
 *
 * @var list<class-string> $components
 */
return [
    'components' => [
        \App\Live\Components\Counter::class,
        \App\Live\Components\Stepper::class,
        \App\Live\Components\TogglePanel::class,
        \App\Live\Components\PropsEcho::class,
        \App\Live\Components\HydrateMeter::class,
    ],
];
