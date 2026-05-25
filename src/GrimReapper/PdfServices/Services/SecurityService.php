<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF security and protection
 */
class SecurityService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'protectpdf';
    }

    /**
     * Protect a PDF document with password or permissions
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Security options
     * @return Document The protected PDF document
     */
    public function protect(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'password' => $options['password'] ?? null,
            'encryptionAlgorithm' => $options['encryptionAlgorithm'] ?? 'AES_256',
            'permissions' => $options['permissions'] ?? []
        ];

        $response = $this->makeRequest('POST', '/operation/protectpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'protected.pdf',
            strlen($resultContent)
        );
    }

    /**
     * Remove protection from a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @param string $password The owner password
     * @return Document The unprotected PDF document
     */
    public function unprotect(string $filePath, string $password): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'password' => $password
        ];

        $response = $this->makeRequest('POST', '/operation/removeprotection', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'unprotected.pdf',
            strlen($resultContent)
        );
    }
}
