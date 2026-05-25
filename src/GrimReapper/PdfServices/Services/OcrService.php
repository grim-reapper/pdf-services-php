<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for Optical Character Recognition (OCR)
 */
class OcrService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'ocr';
    }

    /**
     * Perform OCR on a document to make it searchable
     *
     * @param string $filePath Path to the document
     * @param array $options OCR options
     * @return Document The processed document
     */
    public function ocr(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/ocr', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'ocr_output.pdf',
            $response['size'] ?? null
        );
    }
}
