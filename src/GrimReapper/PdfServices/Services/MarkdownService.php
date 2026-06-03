<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF to Markdown conversion
 *
 * Adobe API endpoint: POST /operation/pdftomarkdown
 *
 * Converts PDF content to well-formatted LLM-friendly Markdown text,
 * with optional embedded base64 figures/images.
 */
class MarkdownService extends AbstractService
{
    public function getServiceName(): string
    {
        return 'pdftomarkdown';
    }

    /**
     * Convert a PDF to Markdown
     *
     * Without renditions, the API returns JSON. With getFigures=true,
     * it returns a ZIP containing markdown + base64 image resources.
     * This method always returns a ZIP by wrapping JSON when necessary.
     *
     * @param string $filePath Path to the PDF file
     * @param array $options [
     *     'getFigures' => bool (default: false) — Include figures/images in base64 format
     * ]
     * @return Document ZIP archive containing markdown and resources
     */
    public function toMarkdown(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID'],
        ];

        if (isset($options['getFigures'])) {
            $requestData['getFigures'] = (bool)$options['getFigures'];
        }

        $response = $this->makeRequest('POST', '/operation/pdftomarkdown', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $assetData = $this->getResultData($jobResult, ['asset', 'content', 'resource']);
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        // Detect whether the API returned a ZIP or raw JSON.
        // ZIP files start with the "PK" magic bytes (0x504b0304).
        $isZip = strlen($resultContent) >= 4 && substr($resultContent, 0, 2) === "PK";

        if ($isZip) {
            return new Document(
                $resultContent,
                'application/zip',
                'markdown.zip',
                strlen($resultContent)
            );
        }

        // The API returned raw JSON (getFigures=false, no renditions).
        // Wrap it in a ZIP so callers can uniformly use saveTo('file.zip').
        return $this->wrapJsonInZip($resultContent, 'markdown.json');
    }

    /**
     * Wrap JSON content in a ZIP archive
     *
     * @param string $jsonContent The JSON content to wrap
     * @param string $entryName The filename inside the ZIP
     * @return Document ZIP archive Document
     */
    private function wrapJsonInZip(string $jsonContent, string $entryName): Document
    {
        if (!class_exists(\ZipArchive::class)) {
            return new Document(
                $jsonContent,
                'application/json',
                $entryName,
                strlen($jsonContent)
            );
        }

        $tempZip = tempnam(sys_get_temp_dir(), 'markdown_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString($entryName, $jsonContent);
        $zip->close();

        $zipContent = file_get_contents($tempZip);
        @unlink($tempZip);

        return new Document(
            $zipContent,
            'application/zip',
            'markdown.zip',
            strlen($zipContent)
        );
    }
}