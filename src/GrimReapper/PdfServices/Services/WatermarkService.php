<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for adding watermarks to PDF documents
 */
class WatermarkService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'addwatermark';
    }

    /**
     * Apply watermark on a PDF
     *
     * @param string $filePath Path to the PDF file
     * @param string $watermarkFilePath Path to the watermark PDF file (first page will be used)
     * @param array $options Watermark options (pageRanges, appearance)
     * @return Document
     */
    public function addWatermark(string $filePath, string $watermarkFilePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $this->validateFile($watermarkFilePath);

        $inputContent = file_get_contents($filePath);
        $watermarkContent = file_get_contents($watermarkFilePath);

        $inputAsset = $this->uploadAsset($inputContent, 'application/pdf');
        $watermarkAsset = $this->uploadAsset($watermarkContent, 'application/pdf');

        $requestData = [
            'inputDocumentAssetID' => $inputAsset['assetID'],
            'watermarkDocumentAssetID' => $watermarkAsset['assetID']
        ];

        if (isset($options['pageRanges'])) {
            $requestData['pageRanges'] = $options['pageRanges'];
        }

        if (isset($options['appearance'])) {
            $requestData['appearance'] = $options['appearance'];
        }

        $response = $this->makeRequest('POST', '/operation/addwatermark', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'watermarked.pdf',
            strlen($resultContent)
        );
    }
}
