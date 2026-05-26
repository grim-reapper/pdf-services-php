<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for splitting PDF documents
 */
class PdfSplitService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'splitpdf';
    }

    /**
     * Split a PDF document into multiple documents
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Split options (e.g., page ranges)
     * @return array Array of Document objects
     */
    public function split(string $filePath, array $options = []): array
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = array_merge([
            'assetID' => $asset['assetID']
        ], $options);

        $response = $this->makeRequest('POST', '/operation/splitpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetsData = $this->getResultData($jobResult, 'assets');

        $results = [];
        foreach ($assetsData as $assetData) {
            $resultContent = $this->httpClient->download($assetData['downloadUri']);
            $results[] = new Document(
                base64_encode($resultContent),
                'application/pdf',
                'split.pdf',
                strlen($resultContent)
            );
        }

        return $results;
    }
}
