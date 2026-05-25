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
        return 'form-data-extraction';
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
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID']
        ];

        $response = $this->makeRequest('POST', '/operation/extractpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['content']['downloadUri']);

        return json_decode($resultContent, true);
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
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'data' => $fieldData
        ];

        $response = $this->makeRequest('POST', '/operation/setformdata', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'filled.pdf',
            strlen($resultContent)
        );
    }
}
