<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Config\Credentials;
use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Http\HttpClient;

/**
 * Service for handling Adobe PDF Services authentication
 */
class AuthService
{
    private PdfServicesConfig $config;
    private HttpClient $httpClient;
    private ?Credentials $credentials = null;

    private const IMS_TOKEN_URL = 'https://ims-na1.adobelogin.com/ims/token/v3';

    /**
     * Create a new auth service
     *
     * @param PdfServicesConfig $config The PDF services configuration
     */
    public function __construct(PdfServicesConfig $config)
    {
        $this->config = $config;
        $this->httpClient = new HttpClient($config);
    }

    /**
     * Get valid credentials (fetches new token if needed)
     *
     * @return Credentials
     */
    public function getCredentials(): Credentials
    {
        if ($this->credentials === null || $this->credentials->isExpired()) {
            $this->credentials = $this->fetchToken();
        }

        return $this->credentials;
    }

    /**
     * Fetch a new access token from Adobe IMS
     *
     * @return Credentials
     */
    private function fetchToken(): Credentials
    {
        $data = [
            'client_id' => $this->config->getClientId(),
            'client_secret' => $this->config->getClientSecret(),
            'grant_type' => 'client_credentials',
            'scope' => 'openid,AdobeID,read_organizations'
        ];

        // IMS token endpoint uses x-www-form-urlencoded
        $body = http_build_query($data);

        $response = $this->httpClient->request(
            'POST',
            self::IMS_TOKEN_URL,
            $body,
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            true // isFullUrl
        );

        return Credentials::fromTokenResponse($response, $this->config);
    }
}
