<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for converting PDF to images
 *
 * Adobe API endpoint: POST /operation/pdftoimages
 *
 * Converts a PDF file into supported image formats (JPEG, PNG, TIFF).
 * Supports two output types:
 * - zipOfPageImages: Returns a ZIP archive containing all page images
 * - listOfPageImages: Returns individual image URLs per page
 */
class PdfToImagesService extends AbstractService
{
    public function getServiceName(): string
    {
        return 'pdftoimages';
    }

    /**
     * Convert a PDF to images
     *
     * @param string $filePath Path to the PDF file
     * @param string $targetFormat Image format: 'jpeg', 'png', or 'tiff'
     * @param string $outputType 'zipOfPageImages' or 'listOfPageImages'
     * @param array $options Additional options
     * @return Document ZIP archive (for zipOfPageImages) or ZIP of images (for listOfPageImages)
     */
    public function toImages(
        string $filePath,
        string $targetFormat = 'jpeg',
        string $outputType = 'zipOfPageImages',
        array $options = []
    ): Document {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID'],
            'targetFormat' => $targetFormat,
            'outputType' => $outputType,
        ];

        $response = $this->makeRequest('POST', '/operation/pdftoimages', $requestData);
        $jobResult = $this->pollJob($response['location']);

        // The API response has an 'assetList' array.
        // For zipOfPageImages: contains 1 entry pointing to a server-side ZIP.
        // For listOfPageImages: contains multiple entries, one per page.
        $assetList = $this->getResultData($jobResult, ['assetList', 'assets']);

        // Normalize: if it's a single assoc array, wrap it
        $items = isset($assetList['downloadUri']) ? [$assetList] : (array)$assetList;

        // S3 pre-signed URLs already contain auth in query parameters;
        // do NOT add Authorization/x-api-key headers (they cause conflicts).

        // zipOfPageImages: the server already created a ZIP, just download and return it
        if ($outputType === 'zipOfPageImages' && count($items) === 1 && isset($items[0]['downloadUri'])) {
            $resultContent = $this->httpClient->download($items[0]['downloadUri']);
            // It's already a valid ZIP from the server
            return new Document(
                $resultContent,
                'application/zip',
                'images.zip',
                strlen($resultContent)
            );
        }

        // listOfPageImages (or fallback): download each image and wrap in a ZIP
        $tempZip = tempnam(sys_get_temp_dir(), 'images_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($items as $index => $imageAsset) {
            $uri = $imageAsset['downloadUri'] ?? ($imageAsset['uri'] ?? '');
            if ($uri) {
                $imageContent = $this->httpClient->download($uri);
                $ext = match ($targetFormat) {
                    'jpeg' => 'jpg',
                    'png' => 'png',
                    'tiff' => 'tiff',
                    default => $targetFormat,
                };
                if (isset($imageAsset['mediaType'])) {
                    $ext = match ($imageAsset['mediaType']) {
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/tiff' => 'tiff',
                        default => $ext
                    };
                }
                $zip->addFromString("page_{$index}.{$ext}", $imageContent);
            }
        }

        $zip->close();
        $zipContent = file_get_contents($tempZip);
        @unlink($tempZip);

        return new Document(
            $zipContent,
            'application/zip',
            'images.zip',
            strlen($zipContent)
        );
    }

    public function toJpeg(string $filePath, string $outputType = 'zipOfPageImages'): Document
    {
        return $this->toImages($filePath, 'jpeg', $outputType);
    }

    public function toPng(string $filePath, string $outputType = 'zipOfPageImages'): Document
    {
        return $this->toImages($filePath, 'png', $outputType);
    }

    public function toTiff(string $filePath, string $outputType = 'zipOfPageImages'): Document
    {
        return $this->toImages($filePath, 'tiff', $outputType);
    }
}
