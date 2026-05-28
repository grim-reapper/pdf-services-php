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
        $contentData = $this->getResultData($jobResult, 'content');
        $resultContent = $this->httpClient->download($contentData['downloadUri']);

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
            'jsonFormFieldsData' => $fieldData
        ];

        $response = $this->makeRequest('POST', '/operation/setformdata', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            $resultContent,
            'application/pdf',
            'filled.pdf',
            strlen($resultContent)
        );
    }

    /**
     * Export form data from a PDF
     *
     * @param Document $document
     * @param string $format 'json' or 'xfdf'
     * @return array|string
     */
    public function exportFormData(Document $document, string $format = 'json'): array|string
    {
        $asset = $this->uploadAsset($document->getContent(), $document->getMimeType());

        $requestData = [
            'assetID' => $asset['assetID'],
            'targetFormat' => $format
        ];

        $response = $this->makeRequest('POST', '/operation/exportpdfformdata', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $contentData = $this->getResultData($jobResult, 'content');
        $resultContent = $this->httpClient->download($contentData['downloadUri']);

        if ($format === 'json') {
            return json_decode($resultContent, true);
        }

        return $resultContent;
    }

    /**
     * Import form data into a PDF
     *
     * @param Document $document
     * @param string $formData Raw XFDF or JSON data
     * @param string $format 'json' or 'xfdf'
     * @return Document
     */
    public function importFormData(Document $document, string $formData, string $format = 'json'): Document
    {
        $pdfAsset = $this->uploadAsset($document->getContent(), $document->getMimeType());
        $dataAsset = $this->uploadAsset($formData, $format === 'json' ? 'application/json' : 'application/vnd.adobe.xfdf');

        $requestData = [
            'assetID' => $pdfAsset['assetID'],
            'formDataAssetID' => $dataAsset['assetID']
        ];

        $response = $this->makeRequest('POST', '/operation/importpdfformdata', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            $resultContent,
            'application/pdf',
            'imported.pdf',
            strlen($resultContent)
        );
    }
}
