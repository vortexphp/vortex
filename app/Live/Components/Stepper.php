<?php

declare(strict_types=1);

namespace App\Live\Components;

use Vortex\Live\Component;

final class Stepper extends Component
{
    public int $value = 0;

    public function view(): string
    {
        return 'live.showcase_stepper';
    }

    public function increment(): void
    {
        $this->value++;
    }

    public function decrement(): void
    {
        $this->value--;
    }

    public function reset(): void
    {
        $this->value = 0;
    }

    public function add(int $delta): void
    {
        $this->value += $delta;
    }
}
