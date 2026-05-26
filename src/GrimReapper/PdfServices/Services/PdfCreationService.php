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

        // Map and filter options for Adobe API v2
        $requestData = [
            'assetID' => $asset['assetID'],
            'json' => $options['json'] ?? '{}'
        ];

        if (isset($options['includeHeaderFooter'])) {
            $requestData['includeHeaderFooter'] = (bool)$options['includeHeaderFooter'];
        }

        if (isset($options['waitTimeToLoad'])) {
            $requestData['waitTimeToLoad'] = (int)$options['waitTimeToLoad'];
        }

        // Handle pageLayout mapping
        $pageLayout = $options['pageLayout'] ?? [];

        // Map 'format' if present and pageLayout not already fully defined
        if (isset($options['format']) && !isset($pageLayout['pageWidth'])) {
            $formats = [
                'A4' => ['width' => 8.27, 'height' => 11.69],
                'Letter' => ['width' => 8.5, 'height' => 11],
                'Legal' => ['width' => 8.5, 'height' => 14],
                'A3' => ['width' => 11.69, 'height' => 16.54],
            ];

            $format = strtoupper($options['format']);
            if (isset($formats[$format])) {
                $pageLayout['pageWidth'] = $formats[$format]['width'];
                $pageLayout['pageHeight'] = $formats[$format]['height'];
            }
        }

        if (!empty($pageLayout)) {
            $requestData['pageLayout'] = $pageLayout;
        }

        $response = $this->makeRequest('POST', '/operation/htmltopdf', $requestData);
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
