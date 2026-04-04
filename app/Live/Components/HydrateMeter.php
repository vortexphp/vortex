<?php

declare(strict_types=1);

namespace App\Live\Components;

use Vortex\Live\Component;

/**
 * Counts how many times snapshot state was applied ({@see hydrated}).
 */
final class HydrateMeter extends Component
{
    public int $hydrations = 0;

    public int $pings = 0;

    public function view(): string
    {
        return 'live.showcase_hydrate_meter';
    }

    protected function hydrated(): void
    {
        $this->hydrations++;
    }

    public function ping(): void
    {
        $this->pings++;
    }
}
