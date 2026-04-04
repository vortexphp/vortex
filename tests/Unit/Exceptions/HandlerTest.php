<?php

declare(strict_types=1);

namespace App\Tests\Unit\Exceptions;

use App\Exceptions\Handler;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Vortex\Container;

final class HandlerTest extends TestCase
{
    public function testDefaultHandlerDefersToFramework(): void
    {
        $handler = new Handler();
        $response = $handler->handle(new RuntimeException('x'), new Container());

        $this->assertNull($response);
    }
}
