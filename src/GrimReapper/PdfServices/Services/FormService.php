<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF form processing
 */
class FormService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'form';
    }

    /**
     * Extract data from a PDF form
     *
     * @param string $filePath Path to the PDF file
     * @return array Extracted form data
     */
    public function extractData(string $filePath): array
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ]
        ];

        $response = $this->makeRequest('POST', '/form/extract', $data);

        return $response['fields'] ?? [];
    }

    /**
     * Fill a PDF form with data
     *
     * @param string $filePath Path to the PDF file
     * @param array $fieldData Data to fill into the form
     * @return Document The filled PDF document
     */
    public function fillForm(string $filePath, array $fieldData): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'fieldData' => $fieldData
        ];

        $response = $this->makeRequest('POST', '/form/fill', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'filled.pdf',
            $response['size'] ?? null
        );
    }
}
