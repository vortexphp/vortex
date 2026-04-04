<?php

declare(strict_types=1);

namespace Vortex\vortex\app\tests\Unit\Database;

use Database\Seeders\DatabaseSeeder;
use PHPUnit\Framework\TestCase;

final class DatabaseSeederTest extends TestCase
{
    public function testRunIsCallable(): void
    {
        (new DatabaseSeeder())->run();

        $this->assertTrue(true);
    }
}
