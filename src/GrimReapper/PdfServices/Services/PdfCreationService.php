<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for creating PDF documents from various sources
 */
class PdfCreationService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'createpdf';
    }

    /**
     * Create a PDF from HTML content
     *
     * @param string $html The HTML content
     * @param array $options Creation options
     * @return Document The created PDF document
     */
    public function fromHtml(string $html, array $options = []): Document
    {
        $asset = $this->uploadAsset($html, 'text/html');

        $data = [
            'assetID' => $asset['assetID'],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/operation/createpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $content = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'document.pdf',
            strlen($content)
        );
    }
}
