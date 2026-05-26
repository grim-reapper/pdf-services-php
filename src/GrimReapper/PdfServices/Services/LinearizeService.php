<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF linearization (Fast Web View)
 */
class LinearizeService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'linearize';
    }

    /**
     * Linearize a PDF for web viewing
     *
     * @param string $filePath
     * @return Document
     */
    public function linearize(string $filePath): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID']
        ];

        $response = $this->makeRequest('POST', '/operation/linearize', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'linearized.pdf',
            strlen($resultContent)
        );
    }
}
