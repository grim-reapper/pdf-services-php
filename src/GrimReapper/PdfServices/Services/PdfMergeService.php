<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for merging multiple PDF documents
 */
class PdfMergeService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'pdf-merge';
    }

    /**
     * Merge multiple PDF documents into one
     *
     * @param array $filePaths Array of paths to PDF files
     * @return Document The merged PDF document
     */
    public function combine(array $filePaths): Document
    {
        $inputs = [];
        foreach ($filePaths as $path) {
            $this->validateFile($path);
            $doc = Document::fromFile($path);
            $inputs[] = [
                'content' => $doc->getContent(),
                'filename' => basename($path)
            ];
        }

        $data = [
            'inputs' => $inputs
        ];

        $response = $this->makeRequest('POST', '/pdf-merge', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'merged.pdf',
            $response['size'] ?? null
        );
    }
}
