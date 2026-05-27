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
        return 'pdfproperties';
    }

    /**
     * Get metadata from a PDF document object
     *
     * @param Document $document
     * @return array
     */
    public function getMetadataFromDocument(Document $document): array
    {
        $asset = $this->uploadAsset(base64_decode($document->getContent()), $document->getMimeType());

        $data = [
            'assetID' => $asset['assetID']
        ];

        $response = $this->makeRequest('POST', '/operation/pdfproperties', $data);
        $jobResult = $this->pollJob($response['location']);

        return $this->getResultData($jobResult, ['pdfProperties', 'metadata']);
    }

    /**
     * Get metadata from a PDF file
     *
     * @param string $filePath Path to the PDF file
     * @return array The document metadata
     */
    public function getMetadata(string $filePath): array
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID']
        ];

        $response = $this->makeRequest('POST', '/operation/pdfproperties', $data);
        $jobResult = $this->pollJob($response['location']);

        return $this->getResultData($jobResult, ['pdfProperties', 'metadata']);
    }

    /**
     * Helper to extract page count from metadata array
     *
     * @param array $metadata
     * @return int
     */
    public static function extractPageCount(array $metadata): int
    {
        return $metadata['document']['pageCount']
            ?? $metadata['document']['page_count']
            ?? $metadata['pdf']['page_count']
            ?? $metadata['pdf']['pageCount']
            ?? $metadata['pageCount']
            ?? $metadata['page_count']
            ?? 0;
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
        throw new \BadMethodCallException('Setting metadata is not directly supported in PDF Services API v2');
    }
}
