<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for compressing PDF documents
 */
class CompressionService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'compresspdf';
    }

    /**
     * Compress a PDF document to reduce its size
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Compression options
     * @return Document The compressed PDF document
     */
    public function compress(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'compressionLevel' => $options['compressionLevel'] ?? 'MEDIUM',
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/compresspdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'compressed.pdf',
            strlen($resultContent)
        );
    }
}
