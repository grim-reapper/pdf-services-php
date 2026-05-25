<?php

declare(strict_types=1);

namespace GrimReapper\Tests;

use GrimReapper\PdfServices\Exceptions\PdfServicesException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testFromApiErrorWithArrayMessage(): void
    {
        $errorResponse = [
            'error' => [
                'message' => 'Nested error message',
                'code' => 'ERROR_CODE'
            ],
            'request-id' => 'req-123'
        ];

        $exception = PdfServicesException::fromApiError($errorResponse, 400);

        $this->assertEquals('Nested error message', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
        $this->assertEquals('req-123', $exception->getRequestId());
        $this->assertEquals($errorResponse, $exception->getDetails());
    }

    public function testFromApiErrorWithStringMessage(): void
    {
        $errorResponse = [
            'error' => 'Simple error message',
            'request-id' => 'req-456'
        ];

        $exception = PdfServicesException::fromApiError($errorResponse, 500);

        $this->assertEquals('Simple error message', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
    }

    public function testFromApiErrorWithRawArrayMessage(): void
    {
        $errorResponse = [
            'error' => ['some' => 'data'],
        ];

        $exception = PdfServicesException::fromApiError($errorResponse, 400);

        $this->assertEquals(json_encode(['some' => 'data']), $exception->getMessage());
    }
}
