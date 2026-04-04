<?php

declare(strict_types=1);

namespace App\Live\Components;

use Vortex\Live\Component;

/**
 * Demonstrates {@see live_mount()} initial props merged before {@see mount()}.
 */
final class PropsEcho extends Component
{
    public string $headline = '';

    public string $tag = '';

    public function view(): string
    {
        return 'live.showcase_props_echo';
    }

    public function mount(): void
    {
        if ($this->tag === '') {
            $this->tag = 'default';
        }
    }

    public function rotateTag(): void
    {
        $this->tag = match ($this->tag) {
            'alpha' => 'beta',
            'beta' => 'gamma',
            default => 'alpha',
        };
    }
}
