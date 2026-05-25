<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF metadata management
 */
class MetadataService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'metadata';
    }

    /**
     * Get metadata from a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @return array The document metadata
     */
    public function getMetadata(string $filePath): array
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ]
        ];

        $response = $this->makeRequest('POST', '/metadata/get', $data);

        return $response['metadata'] ?? [];
    }

    /**
     * Set metadata for a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @param array $metadata The metadata to set
     * @return Document The updated PDF document
     */
    public function setMetadata(string $filePath, array $metadata): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'metadata' => $metadata
        ];

        $response = $this->makeRequest('POST', '/metadata/set', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'metadata_updated.pdf',
            $response['size'] ?? null
        );
    }
}
