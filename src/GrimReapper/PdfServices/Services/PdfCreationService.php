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
        return 'htmltopdf';
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

        // Map and filter options for Adobe API v2 (htmltopdf)
        // Note: htmltopdf is strict and only supports specific keys.
        $requestData = [
            'assetID' => $asset['assetID'],
            'json' => is_array($options['json'] ?? null) ? json_encode($options['json']) : ($options['json'] ?? '{}'),
            'includeHeaderFooter' => (bool)($options['includeHeaderFooter'] ?? false)
        ];

        if (isset($options['waitTimeToLoad'])) {
            $requestData['waitTimeToLoad'] = (int)$options['waitTimeToLoad'];
        }

        // Handle pageLayout mapping (pageWidth and pageHeight in inches)
        $pageLayoutInput = $options['pageLayout'] ?? [];
        $pageLayout = [];

        // Map convenience 'format' key if present
        if (isset($options['format'])) {
            $formats = [
                'A4' => ['width' => 8.27, 'height' => 11.69],
                'LETTER' => ['width' => 8.5, 'height' => 11],
                'LEGAL' => ['width' => 8.5, 'height' => 14],
                'A3' => ['width' => 11.69, 'height' => 16.54],
            ];

            $format = strtoupper((string)$options['format']);
            if (isset($formats[$format])) {
                $pageLayout['pageWidth'] = $formats[$format]['width'];
                $pageLayout['pageHeight'] = $formats[$format]['height'];
            }
        }

        // Explicit pageWidth/pageHeight overrides format
        if (isset($pageLayoutInput['pageWidth'])) $pageLayout['pageWidth'] = (float)$pageLayoutInput['pageWidth'];
        if (isset($pageLayoutInput['pageHeight'])) $pageLayout['pageHeight'] = (float)$pageLayoutInput['pageHeight'];

        // Note: v2 htmltopdf REST API does not support 'margins' in the JSON body.
        // Margins must be defined via CSS @page rules in the HTML content itself.

        if (!empty($pageLayout)) {
            $requestData['pageLayout'] = $pageLayout;
        }

        $this->log('debug', 'Submitting HTML to PDF job', ['request_data' => $requestData]);
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

    /**
     * Create a PDF from a URL
     *
     * @param string $url The URL to convert
     * @param array $options Creation options
     * @return Document The created PDF document
     */
    public function fromUrl(string $url, array $options = []): Document
    {
        $requestData = [
            'inputUrl' => $url,
            'json' => is_array($options['json'] ?? null) ? json_encode($options['json']) : ($options['json'] ?? '{}'),
            'includeHeaderFooter' => (bool)($options['includeHeaderFooter'] ?? false)
        ];

        if (isset($options['waitTimeToLoad'])) {
            $requestData['waitTimeToLoad'] = (int)$options['waitTimeToLoad'];
        }

        // Handle pageLayout mapping
        $pageLayout = [];
        if (isset($options['format'])) {
            $formats = [
                'A4' => ['width' => 8.27, 'height' => 11.69],
                'LETTER' => ['width' => 8.5, 'height' => 11],
            ];
            $format = strtoupper((string)$options['format']);
            if (isset($formats[$format])) {
                $pageLayout['pageWidth'] = $formats[$format]['width'];
                $pageLayout['pageHeight'] = $formats[$format]['height'];
            }
        }

        if (!empty($pageLayout)) {
            $requestData['pageLayout'] = $pageLayout;
        }

        $this->log('debug', 'Submitting URL to PDF job', ['request_data' => $requestData]);
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

    /**
     * Create a PDF from an image file
     *
     * @param string $filePath Path to the image file
     * @return Document The created PDF document
     */
    public function imageToPdf(string $filePath): Document
    {
        return $this->fromFile($filePath);
    }

    /**
     * Create a PDF from a supported file (Word, Excel, PowerPoint, Image, Text)
     *
     * @param string $filePath Path to the file
     * @return Document The created PDF document
     */
    public function fromFile(string $filePath): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'rtf' => 'application/rtf',
            'bmp' => 'image/bmp',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'tiff' => 'image/tiff',
            'png' => 'image/png',
        ];

        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        $asset = $this->uploadAsset($content, $mimeType);

        $requestData = [
            'assetID' => $asset['assetID'],
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/createpdf', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'document.pdf',
            strlen($resultContent)
        );
    }
}
