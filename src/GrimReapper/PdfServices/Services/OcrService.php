<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for Optical Character Recognition (OCR)
 */
class OcrService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'ocr';
    }

    /**
     * Perform OCR on a document to make it searchable
     *
     * @param string $filePath Path to the document
     * @param array $options OCR options
     * @return Document The processed document
     */
    public function ocr(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/operation/ocr', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'ocr_output.pdf',
            strlen($resultContent)
        );
    }
}
