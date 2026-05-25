<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF annotations
 */
class AnnotationService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'annotation';
    }

    /**
     * Add annotations to a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @param array $annotations Array of annotation definitions
     * @return Document The annotated PDF document
     */
    public function addAnnotations(string $filePath, array $annotations): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'annotations' => $annotations
        ];

        $response = $this->makeRequest('POST', '/annotation/add', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'annotated.pdf',
            $response['size'] ?? null
        );
    }

    /**
     * Get all annotations from a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @return array Array of annotations
     */
    public function getAnnotations(string $filePath): array
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ]
        ];

        $response = $this->makeRequest('POST', '/annotation/get', $data);

        return $response['annotations'] ?? [];
    }
}
