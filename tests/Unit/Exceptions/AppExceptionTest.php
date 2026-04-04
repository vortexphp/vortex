<?php

declare(strict_types=1);

namespace App\Tests\Unit\Exceptions;

use App\Exceptions\AppException;
use PHPUnit\Framework\TestCase;

final class AppExceptionTest extends TestCase
{
    public function testIsThrowableWithMessage(): void
    {
        $e = new AppException('domain error');

        $this->assertSame('domain error', $e->getMessage());
    }
}
