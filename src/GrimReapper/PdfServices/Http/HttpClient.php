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
     * @param string $endpoint The API endpoint
     * @param array $data The request data
     * @param array $headers Additional headers
     * @return array The response data
     * @throws ApiException
     * @throws AuthenticationException
     */
    public function request(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): array {
        $url = $this->config->getBaseUrl() . $endpoint;

        // Prepare headers
        $defaultHeaders = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($this->credentials) {
            $defaultHeaders['Authorization'] = $this->credentials->getAuthorizationHeader();
        }

        $headers = array_merge($defaultHeaders, $headers);

        $this->log('debug', "Making {$method} request to {$url}", [
            'headers' => array_keys($headers),
            'data_keys' => array_keys($data)
        ]);

        try {
            if ($this->psrClient && $this->requestFactory && $this->streamFactory) {
                return $this->makePsrRequest($method, $url, $data, $headers);
            } else {
                return $this->makeCurlRequest($method, $url, $data, $headers);
            }
        } catch (\Exception $e) {
            $this->log('error', 'HTTP request failed', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            if ($e instanceof AuthenticationException || $e instanceof ApiException) {
                throw $e;
            }

            throw new ApiException('HTTP request failed: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Make a PSR-18 compatible request
     *
     * @param string $method The HTTP method
     * @param string $url The URL
     * @param array $data The request data
     * @param array $headers The headers
     * @return array The response data
     */
    private function makePsrRequest(string $method, string $url, array $data, array $headers): array
    {
        $request = $this->requestFactory->createRequest($method, $url);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (!empty($data)) {
            $body = json_encode($data);
            $stream = $this->streamFactory->createStream($body);
            $request = $request->withBody($stream);
        }

        $response = $this->psrClient->sendRequest($request);

        $statusCode = $response->getStatusCode();
        $responseBody = $response->getBody()->getContents();

        return $this->handleResponse($statusCode, $responseBody);
    }

    /**
     * Make a cURL request
     *
     * @param string $method The HTTP method
     * @param string $url The URL
     * @param array $data The request data
     * @param array $headers The headers
     * @return array The response data
     */
    private function makeCurlRequest(string $method, string $url, array $data, array $headers): array
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($headers));

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        // Add timeout and other options
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw new ApiException('cURL error: ' . $error);
        }

        return $this->handleResponse($statusCode, $response);
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

        if ($statusCode === 401) {
            throw AuthenticationException::fromApiError(json_decode($responseBody, true) ?: [], $statusCode);
        }

        if ($statusCode >= 400) {
            throw ApiException::fromApiError(json_decode($responseBody, true) ?: [], $statusCode);
        }

        return json_decode($responseBody, true) ?: [];
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
