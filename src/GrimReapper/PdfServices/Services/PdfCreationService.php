<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for creating PDF documents from various sources
 */
class PdfCreationService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'pdf-creation';
    }

    /**
     * Create a PDF from HTML content
     *
     * @param string $html The HTML content
     * @param array $options Creation options
     * @return Document The created PDF document
     */
    public function fromHtml(string $html, array $options = []): Document
    {
        $data = [
            'html' => $html,
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/pdf-creation/html', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'document.pdf',
            $response['size'] ?? null
        );
    }
}
