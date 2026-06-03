<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for merging multiple PDF documents
 */
class PdfMergeService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'combinepdf';
    }

    /**
     * Merge multiple PDF documents into one
     *
     * @param array $filePaths Array of paths to PDF files
     * @return Document The merged PDF document
     */
    public function combine(array $filePaths): Document
    {
        $assets = [];
        foreach ($filePaths as $path) {
            $this->validateFile($path);
            $content = file_get_contents($path);
            $asset = $this->uploadAsset($content, 'application/pdf');
            $assets[] = ['assetID' => $asset['assetID']];
        }

        $data = [
            'assets' => $assets
        ];

        $response = $this->makeRequest('POST', '/operation/combinepdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            $resultContent,
            'application/pdf',
            'merged.pdf',
            strlen($resultContent)
        );
    }
}
