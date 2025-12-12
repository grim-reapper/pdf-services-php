<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Config;

use DateTime;
use DateTimeInterface;

/**
 * Credentials class for Adobe PDF Services API authentication
 *
 * This class manages authentication tokens and handles token refresh
 * for the Adobe PDF Services API.
 */
class Credentials
{
    private string $accessToken;
    private DateTimeInterface $expiresAt;
    private string $tokenType;
    private PdfServicesConfig $config;

    /**
     * Create a new credentials instance
     *
     * @param string $accessToken The access token
     * @param DateTimeInterface $expiresAt When the token expires
     * @param string $tokenType The token type (usually "Bearer")
     * @param PdfServicesConfig $config The PDF services configuration
     */
    public function __construct(
        string $accessToken,
        DateTimeInterface $expiresAt,
        string $tokenType,
        PdfServicesConfig $config
    ) {
        $this->accessToken = $accessToken;
        $this->expiresAt = $expiresAt;
        $this->tokenType = $tokenType;
        $this->config = $config;
    }

    /**
     * Get the access token
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Get when the token expires
     *
     * @return DateTimeInterface
     */
    public function getExpiresAt(): DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * Get the token type
     *
     * @return string
     */
    public function getTokenType(): string
    {
        return $this->tokenType;
    }

    /**
     * Check if the token is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTime();
    }

    /**
     * Get the authorization header value
     *
     * @return string
     */
    public function getAuthorizationHeader(): string
    {
        return $this->tokenType . ' ' . $this->accessToken;
    }

    /**
     * Get the configuration
     *
     * @return PdfServicesConfig
     */
    public function getConfig(): PdfServicesConfig
    {
        return $this->config;
    }

    /**
     * Create credentials from JWT token response
     *
     * @param array $tokenResponse The token response from Adobe
     * @param PdfServicesConfig $config The PDF services configuration
     * @return self
     */
    public static function fromTokenResponse(array $tokenResponse, PdfServicesConfig $config): self
    {
        $expiresAt = new DateTime();
        $expiresAt->setTimestamp(time() + ($tokenResponse['expires_in'] ?? 3600));

        return new self(
            $tokenResponse['access_token'],
            $expiresAt,
            $tokenResponse['token_type'] ?? 'Bearer',
            $config
        );
    }
}
