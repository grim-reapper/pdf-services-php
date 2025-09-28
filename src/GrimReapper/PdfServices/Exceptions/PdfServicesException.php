<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Exceptions;

use Exception;

/**
 * Base exception for Adobe PDF Services
 *
 * This is the base exception class for all PDF Services related errors.
 */
class PdfServicesException extends Exception
{
    private ?string $requestId;
    private ?array $details;

    /**
     * Create a new PDF Services exception
     *
     * @param string $message The error message
     * @param int $code The error code
     * @param string|null $requestId The request ID from Adobe API
     * @param array|null $details Additional error details
     * @param Exception|null $previous The previous exception
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?string $requestId = null,
        ?array $details = null,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->requestId = $requestId;
        $this->details = $details;
    }

    /**
     * Get the request ID
     *
     * @return string|null
     */
    public function getRequestId(): ?string
    {
        return $this->requestId;
    }

    /**
     * Get error details
     *
     * @return array|null
     */
    public function getDetails(): ?array
    {
        return $this->details;
    }

    /**
     * Create exception from API error response
     *
     * @param array $errorResponse The error response from Adobe API
     * @param int $httpCode The HTTP status code
     * @return static
     */
    public static function fromApiError(array $errorResponse, int $httpCode = 0): self
    {
        $message = $errorResponse['error_description'] ?? $errorResponse['error'] ?? 'Unknown API error';
        $requestId = $errorResponse['request-id'] ?? null;
        $details = $errorResponse;

        return new static($message, $httpCode, $requestId, $details);
    }
}
