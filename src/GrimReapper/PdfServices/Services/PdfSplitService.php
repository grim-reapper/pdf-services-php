<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for splitting PDF documents
 */
class PdfSplitService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'pdf-split';
    }

    /**
     * Split a PDF document into multiple documents
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Split options (e.g., page ranges)
     * @return array Array of Document objects
     */
    public function split(string $filePath, array $options = []): array
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/pdf-split', $data);

        $results = [];
        foreach ($response['documents'] as $docData) {
            $results[] = new Document(
                $docData['content'],
                'application/pdf',
                $docData['filename'] ?? 'split.pdf',
                $docData['size'] ?? null
            );
        }

        return $results;
    }
}
