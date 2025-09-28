<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\Contracts\ServiceInterface;
use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Exceptions\ApiException;
use GrimReapper\PdfServices\Exceptions\AuthenticationException;
use GrimReapper\PdfServices\Exceptions\PdfServicesException;
use GrimReapper\PdfServices\Http\HttpClient;
use Psr\Log\LoggerInterface;

/**
 * Abstract base class for all PDF Services
 */
abstract class AbstractService implements ServiceInterface
{
    protected PdfServicesConfig $config;
    protected HttpClient $httpClient;
    protected ?LoggerInterface $logger;

    /**
     * Create a new service instance
     *
     * @param PdfServicesConfig $config The PDF services configuration
     */
    public function __construct(PdfServicesConfig $config)
    {
        $this->config = $config;
        $this->httpClient = new HttpClient($config);
        $this->logger = null;
    }

    /**
     * Get the service configuration
     *
     * @return PdfServicesConfig
     */
    public function getConfig(): PdfServicesConfig
    {
        return $this->config;
    }

    /**
     * Get the service name
     *
     * @return string
     */
    abstract public function getServiceName(): string;

    /**
     * Set a logger for the service
     *
     * @param LoggerInterface $logger The logger instance
     * @return self
     */
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        $this->httpClient->setLogger($logger);
        return $this;
    }

    /**
     * Get the logger
     *
     * @return LoggerInterface|null
     */
    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Log a message
     *
     * @param string $level The log level
     * @param string $message The message
     * @param array $context Additional context
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        if ($this->logger) {
            $this->logger->log($level, $message, $context);
        }
    }

    /**
     * Make an API request
     *
     * @param string $method The HTTP method
     * @param string $endpoint The API endpoint
     * @param array $data The request data
     * @param array $headers Additional headers
     * @return array The response data
     * @throws PdfServicesException
     */
    protected function makeRequest(
        string $method,
        string $endpoint,
        array $data = [],
        array $headers = []
    ): array {
        try {
            $this->log('info', "Making {$method} request to {$endpoint}", [
                'service' => $this->getServiceName(),
                'data' => $data
            ]);

            $response = $this->httpClient->request($method, $endpoint, $data, $headers);

            $this->log('info', "Request completed successfully", [
                'service' => $this->getServiceName(),
                'endpoint' => $endpoint
            ]);

            return $response;

        } catch (AuthenticationException $e) {
            $this->log('error', 'Authentication failed', [
                'service' => $this->getServiceName(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (ApiException $e) {
            $this->log('error', 'API request failed', [
                'service' => $this->getServiceName(),
                'endpoint' => $endpoint,
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (PdfServicesException $e) {
            $this->log('error', 'PDF Services error', [
                'service' => $this->getServiceName(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->log('error', 'Unexpected error', [
                'service' => $this->getServiceName(),
                'error' => $e->getMessage()
            ]);
            throw new ApiException('Unexpected error: ' . $e->getMessage(), 0, null, null, $e);
        }
    }

    /**
     * Validate required parameters
     *
     * @param array $params The parameters to validate
     * @param array $required The required parameter names
     * @throws \InvalidArgumentException
     */
    protected function validateRequiredParams(array $params, array $required): void
    {
        foreach ($required as $param) {
            if (!isset($params[$param]) || $params[$param] === null || $params[$param] === '') {
                throw new \InvalidArgumentException("Required parameter '{$param}' is missing or empty");
            }
        }
    }

    /**
     * Validate file exists and is readable
     *
     * @param string $filePath The file path to validate
     * @throws \InvalidArgumentException
     */
    protected function validateFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("File does not exist: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new \InvalidArgumentException("File is not readable: {$filePath}");
        }

        if (!is_file($filePath)) {
            throw new \InvalidArgumentException("Path is not a file: {$filePath}");
        }
    }
}
