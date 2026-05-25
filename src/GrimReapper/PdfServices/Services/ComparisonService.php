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
        return 'comparison';
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

        $baseDoc = Document::fromFile($baseFilePath);
        $comparisonDoc = Document::fromFile($comparisonFilePath);

        $data = [
            'base' => [
                'content' => $baseDoc->getContent()
            ],
            'comparison' => [
                'content' => $comparisonDoc->getContent()
            ]
        ];

        $response = $this->makeRequest('POST', '/comparison/compare', $data);

        return ComparisonResult::fromApiResponse($response);
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

        $baseDoc = Document::fromFile($baseFilePath);
        $comparisonDoc = Document::fromFile($comparisonFilePath);

        $data = [
            'base' => [
                'content' => $baseDoc->getContent()
            ],
            'comparison' => [
                'content' => $comparisonDoc->getContent()
            ]
        ];

        $response = $this->makeRequest('POST', '/comparison/diff-report', $data);

        $document = new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'diff_report.pdf',
            $response['size'] ?? null
        );

        if ($outputPath) {
            $document->saveTo($outputPath);
        }

        return $document;
    }
}
