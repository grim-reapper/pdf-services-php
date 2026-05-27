<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;
use GrimReapper\PdfServices\Models\ComparisonResult;

/**
 * Service for comparing PDF documents
 */
class ComparisonService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'comparepdf';
    }

    /**
     * Compare two PDF documents
     *
     * @param string $baseFilePath Path to the base PDF file
     * @param string $comparisonFilePath Path to the comparison PDF file
     * @return ComparisonResult The result of the comparison
     */
    public function comparePdfs(string $baseFilePath, string $comparisonFilePath): ComparisonResult
    {
        $this->validateFile($baseFilePath);
        $this->validateFile($comparisonFilePath);

        $baseContent = file_get_contents($baseFilePath);
        $comparisonContent = file_get_contents($comparisonFilePath);

        $baseAsset = $this->uploadAsset($baseContent, 'application/pdf');
        $comparisonAsset = $this->uploadAsset($comparisonContent, 'application/pdf');

        $data = [
            'baseAssetID' => $baseAsset['assetID'],
            'comparisonAssetID' => $comparisonAsset['assetID'],
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/comparepdf', $data);
        $jobResult = $this->pollJob($response['location']);

        return ComparisonResult::fromApiResponse($jobResult);
    }

    /**
     * Generate a visual diff report between two PDF documents
     *
     * @param string $baseFilePath Path to the base PDF file
     * @param string $comparisonFilePath Path to the comparison PDF file
     * @param string|null $outputPath Optional path to save the report
     * @return Document The diff report document
     */
    public function generateDiffReport(
        string $baseFilePath,
        string $comparisonFilePath,
        ?string $outputPath = null
    ): Document {
        $this->validateFile($baseFilePath);
        $this->validateFile($comparisonFilePath);

        $baseContent = file_get_contents($baseFilePath);
        $comparisonContent = file_get_contents($comparisonFilePath);

        $baseAsset = $this->uploadAsset($baseContent, 'application/pdf');
        $comparisonAsset = $this->uploadAsset($comparisonContent, 'application/pdf');

        $data = [
            'baseAssetID' => $baseAsset['assetID'],
            'comparisonAssetID' => $comparisonAsset['assetID'],
            'includeDiffReport' => true,
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/comparepdf', $data);
        $jobResult = $this->pollJob($response['location']);

        $diffReportData = $this->getResultData($jobResult, 'diffReport');
        $resultContent = $this->httpClient->download($diffReportData['downloadUri']);

        $document = new Document(
            base64_encode($resultContent),
            'application/pdf',
            'diff_report.pdf',
            strlen($resultContent)
        );

        if ($outputPath) {
            $document->saveTo($outputPath);
        }

        return $document;
    }
}
