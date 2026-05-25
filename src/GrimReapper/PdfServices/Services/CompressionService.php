<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for compressing PDF documents
 */
class CompressionService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'compression';
    }

    /**
     * Compress a PDF document to reduce its size
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Compression options
     * @return Document The compressed PDF document
     */
    public function compress(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/compress', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'compressed.pdf',
            $response['size'] ?? null
        );
    }
}
