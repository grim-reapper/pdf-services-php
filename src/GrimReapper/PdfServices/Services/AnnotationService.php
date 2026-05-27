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
            'annotations' => $annotations,
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/pdf-annotations', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

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
            'assetID' => $asset['assetID'],
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/pdf-annotations/get', $data);
        $jobResult = $this->pollJob($response['location']);

        return $this->getResultData($jobResult, 'annotations');
    }
}
