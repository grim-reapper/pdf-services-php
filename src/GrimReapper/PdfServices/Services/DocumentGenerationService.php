<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for Adobe Document Generation
 */
class DocumentGenerationService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'documentgeneration';
    }

    /**
     * Generate a document from a Word template and JSON data
     *
     * @param string $templatePath Path to the .docx template
     * @param array|string $jsonData Data to merge
     * @param string $outputFormat 'pdf' or 'docx'
     * @return Document
     */
    public function generate(string $templatePath, $jsonData, string $outputFormat = 'pdf'): Document
    {
        $this->validateFile($templatePath);
        $templateContent = file_get_contents($templatePath);
        $asset = $this->uploadAsset($templateContent, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $requestData = [
            'assetID' => $asset['assetID'],
            'outputFormat' => $outputFormat,
            'jsonDataForMerge' => is_string($jsonData) ? json_decode($jsonData, true) : $jsonData,
        ];

        $response = $this->makeRequest('POST', '/operation/documentgeneration', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            $resultContent,
            $outputFormat === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'generated.' . $outputFormat,
            strlen($resultContent)
        );
    }
}
