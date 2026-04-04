<?php

declare(strict_types=1);

namespace App\Live\Components;

use Vortex\Live\Component;

final class TogglePanel extends Component
{
    public bool $open = false;

    public function view(): string
    {
        return 'live.showcase_toggle';
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }
}
