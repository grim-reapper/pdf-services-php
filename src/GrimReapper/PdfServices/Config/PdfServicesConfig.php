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
    private string $apiKey;
    private string $clientId;
    private string $organizationId;
    private string $environment;
    private ?ClientInterface $httpClient;
    private ?RequestFactoryInterface $requestFactory;
    private ?StreamFactoryInterface $streamFactory;
    private array $httpOptions;

    /**
     * Create a new PDF Services configuration
     *
     * @param string $apiKey Adobe PDF Services API key
     * @param string $clientId Adobe client ID
     * @param string $organizationId Adobe organization ID
     * @param string $environment API environment ('production' or 'staging')
     * @param array $httpOptions Additional HTTP client options
     */
    public function __construct(
        string $apiKey,
        string $clientId,
        string $organizationId,
        string $environment = 'production',
        array $httpOptions = []
    ) {
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
        $this->organizationId = $organizationId;
        $this->environment = $environment;
        $this->httpOptions = $httpOptions;
        $this->httpClient = null;
        $this->requestFactory = null;
        $this->streamFactory = null;
    }

    /**
     * Get the API key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->apiKey;
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
     * Get the organization ID
     *
     * @return string
     */
    public function getOrganizationId(): string
    {
        return $this->organizationId;
    }

    /**
     * Get the API environment
     *
     * @return string
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }

    /**
     * Get the base URL for the API
     *
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->environment === 'production'
            ? 'https://pdf-services.adobe.io'
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
     * Create configuration from environment variables
     *
     * @return self
     */
    public static function fromEnvironment(): self
    {
        return new self(
            getenv('GRIM_REAPPER_PDF_SERVICES_API_KEY') ?: '',
            getenv('GRIM_REAPPER_PDF_SERVICES_CLIENT_ID') ?: '',
            getenv('GRIM_REAPPER_PDF_SERVICES_ORGANIZATION_ID') ?: '',
            getenv('GRIM_REAPPER_PDF_SERVICES_ENVIRONMENT') ?: 'production'
        );
    }
}
