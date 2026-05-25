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
     * Get metadata from a PDF document
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

        return $jobResult['result']['pdfProperties'] ?? [];
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
        // Adobe PDF Services doesn't have a direct 'set metadata' operation in v2.
        // It's usually part of other operations or achieved via Document Generation.
        // For now, we'll keep the structure but it might not be supported directly.
        throw new \BadMethodCallException('Setting metadata is not directly supported in PDF Services API v2');
    }
}
