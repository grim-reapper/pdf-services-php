<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF to Markdown conversion
 */
class MarkdownService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'pdftomarkdown';
    }

    /**
     * Convert a PDF to Markdown
     *
     * @param string $filePath
     * @return Document ZIP archive containing markdown and resources
     */
    public function toMarkdown(string $filePath): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID']
        ];

        $response = $this->makeRequest('POST', '/operation/pdftomarkdown', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/zip',
            'markdown.zip',
            strlen($resultContent)
        );
    }
}
