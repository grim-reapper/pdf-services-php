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
        // Wrap HTML in a ZIP if it's just raw HTML content, as the API often requires
        // a ZIP containing index.html and its resources.
        $tempFile = tempnam(sys_get_temp_dir(), 'html') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE) === TRUE) {
            $zip->addFromString('index.html', $html);
            $zip->close();
            $content = file_get_contents($tempFile);
            unlink($tempFile);
            $mediaType = 'application/zip';
        } else {
            $content = $html;
            $mediaType = 'text/html';
        }

        $asset = $this->uploadAsset($content, $mediaType);

        $data = array_merge([
            'assetID' => $asset['assetID'],
            'json' => '{}'
        ], $options);

        $response = $this->makeRequest('POST', '/operation/htmltopdf', $data);
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
