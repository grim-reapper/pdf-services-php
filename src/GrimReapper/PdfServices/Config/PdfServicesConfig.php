<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Config;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Configuration class for Adobe PDF Services API
 *
 * This class holds all the configuration parameters needed to connect
 * to and authenticate with the Adobe PDF Services API.
 */
class PdfServicesConfig
{
    private string $clientId;
    private string $clientSecret;
    private string $organizationId;
    private string $region;
    private ?ClientInterface $httpClient;
    private ?RequestFactoryInterface $requestFactory;
    private ?StreamFactoryInterface $streamFactory;
    private array $httpOptions;
    private array $notifiers = [];

    /**
     * Create a new PDF Services configuration
     *
     * @param string $clientId Adobe client ID
     * @param string $clientSecret Adobe client secret
     * @param string $organizationId Adobe organization ID
     * @param string $region API region ('us' or 'eu')
     * @param array $httpOptions Additional HTTP client options
     */
    public function __construct(
        string $clientId,
        string $clientSecret,
        string $organizationId,
        string $region = 'us',
        array $httpOptions = []
    ) {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->organizationId = $organizationId;
        $this->region = strtolower($region);
        $this->httpOptions = $httpOptions;
        $this->httpClient = null;
        $this->requestFactory = null;
        $this->streamFactory = null;
    }

    /**
     * Get the client ID
     *
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * Get the client secret
     *
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * Get the organization ID
     *
     * @return string
     */
    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    /**
     * Get the API region
     *
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * Get the base URL for the API
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->region === 'eu'
            ? 'https://pdf-services-ew1.adobe.io'
            : 'https://pdf-services-ue1.adobe.io';
    }

    /**
     * Set a custom HTTP client
     *
     * @param ClientInterface $httpClient
     * @return self
     */
    public function setHttpClient(ClientInterface $httpClient): self
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Get the HTTP client
     *
     * @return ClientInterface|null
     */
    public function getHttpClient(): ?ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Set a custom request factory
     *
     * @param RequestFactoryInterface $requestFactory
     * @return self
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;
        return $this;
    }

    /**
     * Get the request factory
     *
     * @return RequestFactoryInterface|null
     */
    public function getRequestFactory(): ?RequestFactoryInterface
    {
        return $this->requestFactory;
    }

    /**
     * Set a custom stream factory
     *
     * @param StreamFactoryInterface $streamFactory
     * @return self
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;
        return $this;
    }

    /**
     * Get the stream factory
     *
     * @return StreamFactoryInterface|null
     */
    public function getStreamFactory(): ?StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * Get HTTP options
     *
     * @return array
     */
    public function getHttpOptions(): array
    {
        return $this->httpOptions;
    }

    /**
     * Set HTTP options
     *
     * @param array $httpOptions
     * @return self
     */
    public function setHttpOptions(array $httpOptions): self
    {
        $this->httpOptions = $httpOptions;
        return $this;
    }

    /**
     * Set notifiers for job completion
     *
     * @param array $notifiers
     * @return self
     */
    public function setNotifiers(array $notifiers): self
    {
        $this->notifiers = $notifiers;
        return $this;
    }

    /**
     * Get notifiers
     *
     * @return array
     */
    public function getNotifiers(): array
    {
        return $this->notifiers;
    }

    /**
     * Create configuration from environment variables
     *
     * @return self
     */
    public static function fromEnvironment(): self
    {
        return new self(
            getenv('GRIM_REAPPER_PDF_SERVICES_CLIENT_ID') ?: '',
            getenv('GRIM_REAPPER_PDF_SERVICES_CLIENT_SECRET') ?: '',
            getenv('GRIM_REAPPER_PDF_SERVICES_ORGANIZATION_ID') ?: '',
            getenv('GRIM_REAPPER_PDF_SERVICES_REGION') ?: 'us'
        );
    }
}
