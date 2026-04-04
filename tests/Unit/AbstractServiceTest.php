<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Services\AbstractService;
use PHPUnit\Framework\TestCase;

final class AbstractServiceTest extends TestCase
{
    public function testConcreteServiceExtendsBase(): void
    {
        $service = new class () extends AbstractService {
        };

        $this->assertInstanceOf(AbstractService::class, $service);
    }
}
