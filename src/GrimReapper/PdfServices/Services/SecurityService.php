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
        return 'security';
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
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/security/protect', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'protected.pdf',
            $response['size'] ?? null
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
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'password' => $password
        ];

        $response = $this->makeRequest('POST', '/security/unprotect', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'unprotected.pdf',
            $response['size'] ?? null
        );
    }
}
