<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF accessibility features
 */
class AccessibilityService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'accessibility';
    }

    /**
     * Auto-tag a PDF for accessibility
     *
     * @param string $filePath
     * @param array $options (shiftHeadings, generateReport)
     * @return array [Document, Document (report)]
     */
    public function autoTag(string $filePath, array $options = []): array
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID'],
        ];

        if (isset($options['shiftHeadings'])) $requestData['shiftHeadings'] = (bool)$options['shiftHeadings'];
        if (isset($options['generateReport'])) $requestData['generateReport'] = (bool)$options['generateReport'];

        $response = $this->makeRequest('POST', '/operation/autotag', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $taggedAssetData = $this->getResultData($jobResult, 'asset');
        $taggedContent = $this->httpClient->download($taggedAssetData['downloadUri']);

        $taggedDoc = new Document(
            $taggedContent,
            'application/pdf',
            'tagged.pdf',
            strlen($taggedContent)
        );

        $reportDoc = null;
        if (isset($jobResult['report'])) {
            $reportContent = $this->httpClient->download($jobResult['report']['downloadUri']);
            $reportDoc = new Document(
                $reportContent,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'report.xlsx',
                strlen($reportContent)
            );
        }

        return [$taggedDoc, $reportDoc];
    }

    /**
     * Check accessibility of a PDF
     *
     * @param string $filePath
     * @param array $options (pageStart, pageEnd)
     * @return Document HTML report
     */
    public function check(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID'],
        ];

        if (isset($options['pageStart'])) $requestData['pageStart'] = (int)$options['pageStart'];
        if (isset($options['pageEnd'])) $requestData['pageEnd'] = (int)$options['pageEnd'];

        $response = $this->makeRequest('POST', '/operation/pdfaccessibilitychecker', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            $resultContent,
            'text/html',
            'accessibility_report.html',
            strlen($resultContent)
        );
    }
}
