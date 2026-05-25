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
        return 'pdf-annotations';
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
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'annotations' => $annotations
        ];

        // Annotation operations in v2 are often handled via specific tools or jobs.
        // If not directly in 'operation', this might need adjustment.
        $response = $this->makeRequest('POST', '/operation/pdf-annotations', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'annotated.pdf',
            strlen($resultContent)
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
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID']
        ];

        $response = $this->makeRequest('POST', '/operation/pdf-annotations/get', $data);
        $jobResult = $this->pollJob($response['location']);

        // This is a placeholder logic based on expected behavior
        return $jobResult['result']['annotations'] ?? [];
    }
}
