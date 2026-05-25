<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Http;

use GrimReapper\PdfServices\Config\Credentials;
use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Exceptions\ApiException;
use GrimReapper\PdfServices\Exceptions\AuthenticationException;
use Psr\Http\Client\ClientInterface as PsrClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;

/**
 * HTTP client for Adobe PDF Services API
 */
class HttpClient
{
    private PdfServicesConfig $config;
    private ?PsrClientInterface $psrClient;
    private ?RequestFactoryInterface $requestFactory;
    private ?StreamFactoryInterface $streamFactory;
    private ?Credentials $credentials;
    private ?LoggerInterface $logger;
    private ?\GrimReapper\PdfServices\Services\AuthService $authService = null;

    /**
     * Create a new HTTP client
     *
     * @param PdfServicesConfig $config The PDF services configuration
     */
    public function __construct(PdfServicesConfig $config)
    {
        $this->config = $config;
        $this->psrClient = $config->getHttpClient();
        $this->requestFactory = $config->getRequestFactory();
        $this->streamFactory = $config->getStreamFactory();
        $this->credentials = null;
        $this->logger = null;
    }

    /**
     * Set the auth service
     *
     * @param \GrimReapper\PdfServices\Services\AuthService $authService
     * @return self
     */
    public function setAuthService(\GrimReapper\PdfServices\Services\AuthService $authService): self
    {
        $this->authService = $authService;
        return $this;
    }

    /**
     * Set the logger
     *
     * @param LoggerInterface $logger The logger instance
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set credentials for authentication
     *
     * @param Credentials $credentials The credentials
     * @return self
     */
    public function setCredentials(Credentials $credentials): self
    {
        $this->credentials = $credentials;
        return $this;
    }

    /**
     * Make an HTTP request
     *
     * @param string $method The HTTP method
     * @param string $url The URL or endpoint
     * @param mixed $data The request data
     * @param array $headers Additional headers
     * @param bool $isFullUrl Whether the URL is a full URL
     * @return array The response data
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function request(
        string $method,
        string $url,
        mixed $data = [],
        array $headers = [],
        bool $isFullUrl = false
    ): array {
        if (!$isFullUrl) {
            $url = $this->config->getBaseUrl() . $url;
        }

        $isAdobeApi = strpos($url, 'adobe.io') !== false;
        $isAuthRequest = strpos($url, 'adobelogin.com') !== false;

        // Prepare headers
        $defaultHeaders = [
            'Accept' => 'application/json',
        ];

        if ($isAdobeApi && !$isAuthRequest) {
            $defaultHeaders['x-api-key'] = $this->config->getClientId();

            if ($this->config->getOrganizationId()) {
                $defaultHeaders['x-gw-ims-org-id'] = $this->config->getOrganizationId();
            }

            // Auto-authenticate if authService is present
            if ($this->authService) {
                $this->credentials = $this->authService->getCredentials();
            }

            if ($this->credentials) {
                $defaultHeaders['Authorization'] = $this->credentials->getAuthorizationHeader();
            }
        }

        if (is_array($data) && !empty($data)) {
            $defaultHeaders['Content-Type'] = 'application/json';
        }

        $headers = array_merge($defaultHeaders, $headers);

        $this->log('debug', "Making {$method} request to {$url}", [
            'headers' => array_keys($headers),
            'data_keys' => is_array($data) ? array_keys($data) : 'binary'
        ]);

        try {
            if ($this->psrClient && $this->requestFactory && $this->streamFactory) {
                return $this->makePsrRequest($method, $url, $data, $headers);
            } else {
                return $this->makeCurlRequest($method, $url, $data, $headers);
            }
        } catch (AuthenticationException|ApiException $e) {
            $this->log('error', 'API error', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->log('error', 'HTTP request failed', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Download a file from a URL
     *
     * @param string $url The URL to download from
     * @return string The file content
     */
    public function download(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);

        $content = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new ApiException("Failed to download file from {$url}. Status code: {$statusCode}");
        }

        return (string)$content;
    }

    /**
     * Make a PSR-18 compatible request
     *
     * @param string $method The HTTP method
     * @param string $url The URL
     * @param mixed $data The request data
     * @param array $headers The headers
     * @return array The response data
     */
    private function makePsrRequest(string $method, string $url, mixed $data, array $headers): array
    {
        $request = $this->requestFactory->createRequest($method, $url);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (!empty($data)) {
            $body = is_array($data) ? json_encode($data) : $data;
            $stream = $this->streamFactory->createStream($body);
            $request = $request->withBody($stream);
        }

        $response = $this->psrClient->sendRequest($request);

        $statusCode = $response->getStatusCode();
        // Check for Location header (common in asynchronous APIs)
        if ($statusCode === 201 || $statusCode === 202) {
            $location = $response->getHeaderLine('Location');
            if ($location) {
                return ['location' => $location, 'status_code' => $statusCode];
            }
        }

        $responseBody = $response->getBody()->getContents();

        return $this->handleResponse($statusCode, $responseBody);
    }

    /**
     * Make a cURL request
     *
     * @param string $method The HTTP method
     * @param string $url The URL
     * @param mixed $data The request data
     * @param array $headers The headers
     * @return array The response data
     */
    private function makeCurlRequest(string $method, string $url, mixed $data, array $headers): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));
        curl_setopt($ch, CURLOPT_HEADER, true); // Include headers in output

        if (!empty($data)) {
            $body = is_array($data) ? json_encode($data) : $data;
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        // Add timeout and other options
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new ApiException('cURL error: ' . $error);
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headerStr = substr((string)$response, 0, $headerSize);
        $body = substr((string)$response, $headerSize);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode === 201 || $statusCode === 202) {
            if (preg_match('/Location: (.*)/i', $headerStr, $matches)) {
                return ['location' => trim($matches[1]), 'status_code' => $statusCode];
            }
        }

        return $this->handleResponse($statusCode, $body);
    }

    /**
     * Handle HTTP response
     *
     * @param int $statusCode The HTTP status code
     * @param string $responseBody The response body
     * @return array The parsed response data
     * @throws ApiException
     * @throws AuthenticationException
     */
    private function handleResponse(int $statusCode, string $responseBody): array
    {
        $this->log('debug', "Received response with status {$statusCode}");

        $data = json_decode($responseBody, true);
        $errorData = $data ?: $responseBody;

        if ($statusCode === 401) {
            throw AuthenticationException::fromApiError($errorData, $statusCode);
        }

        if ($statusCode >= 400) {
            throw ApiException::fromApiError($errorData, $statusCode);
        }

        return $data ?: [];
    }

    /**
     * Format headers for cURL
     *
     * @param array $headers The headers array
     * @return array The formatted headers
     */
    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $name => $value) {
            $formatted[] = "{$name}: {$value}";
        }
        return $formatted;
    }

    /**
     * Log a message
     *
     * @param string $level The log level
     * @param string $message The message
     * @param array $context Additional context
     * @return void
     */
    private function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }
}
