<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF extraction
 */
class ExtractService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'extractpdf';
    }

    /**
     * Extract content from a PDF
     *
     * When extracting only text/tables (no renditions), the Adobe API returns
     * JSON directly. When renditions (figures, table images) are requested,
     * the API returns a ZIP archive.
     *
     * This method always returns a ZIP: if the API returns raw JSON, it wraps
     * it in a ZIP archive so callers can uniformly use saveTo('file.zip').
     *
     * @param string $filePath
     * @param array $elements List of elements to extract (text, tables, figures)
     * @param array $options Additional options (getCharBounds, includeStyling, renditionsToExtract)
     * @return Document ZIP archive containing JSON and renditions
     */
    public function extract(string $filePath, array $elements = ['text'], array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID'],
            'elementsToExtract' => $elements
        ];

        if (isset($options['getCharBounds'])) $requestData['getCharBounds'] = (bool)$options['getCharBounds'];
        if (isset($options['includeStyling'])) $requestData['includeStyling'] = (bool)$options['includeStyling'];
        if (isset($options['tableOutputFormat'])) $requestData['tableOutputFormat'] = $options['tableOutputFormat'];
        if (isset($options['renditionsToExtract'])) $requestData['renditionsToExtract'] = $options['renditionsToExtract'];

        $response = $this->makeRequest('POST', '/operation/extractpdf', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $assetData = $this->getResultData($jobResult, ['content', 'asset', 'resource']);
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        // Detect whether the API returned a ZIP or raw JSON.
        // ZIP files start with the "PK" magic bytes (0x504b0304).
        $isZip = strlen($resultContent) >= 4 && substr($resultContent, 0, 2) === "PK";

        if ($isZip) {
            return new Document(
                $resultContent,
                'application/zip',
                'extract.zip',
                strlen($resultContent)
            );
        }

        // The API returned JSON (text/tables without renditions).
        // Wrap it in a ZIP so callers can still use saveTo('file.zip').
        return $this->wrapJsonInZip($resultContent, 'structuredData.json');
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
            // Fallback: return raw JSON if zip extension is unavailable
            return new Document(
                $jsonContent,
                'application/json',
                'structuredData.json',
                strlen($jsonContent)
            );
        }

        $tempZip = tempnam(sys_get_temp_dir(), 'extract_') . '.zip';
        $zip = new \ZipArchive();
        $zip->open($tempZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString($entryName, $jsonContent);
        $zip->close();

        $zipContent = file_get_contents($tempZip);
        @unlink($tempZip);

        return new Document(
            $zipContent,
            'application/zip',
            'extract.zip',
            strlen($zipContent)
        );
    }
}
