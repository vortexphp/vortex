<?php

declare(strict_types=1);

namespace App\Live;

use Vortex\Live\Component;

final class Counter extends Component
{
    public int $count = 0;

    public function view(): string
    {
        return 'live.counter';
    }

    public function increment(): void
    {
        $this->count++;
    }
}
