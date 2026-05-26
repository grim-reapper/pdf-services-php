<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for page manipulation operations (Insert, Delete, Replace, Reorder, Rotate)
 */
class PageManipulationService extends AbstractService
{
    public function getServiceName(): string
    {
        return 'pagemanipulation';
    }

    /**
     * Delete pages from a PDF
     *
     * @param Document $document
     * @param array $pageRanges Array of ['start' => 1, 'end' => 2]
     * @return Document
     */
    public function deletePages(Document $document, array $pageRanges): Document
    {
        $asset = $this->uploadAsset(base64_decode($document->getContent()), $document->getMimeType());

        $requestData = [
            'assetID' => $asset['assetID'],
            'pageActions' => [
                [
                    'delete' => [
                        'pageRanges' => $pageRanges
                    ]
                ]
            ]
        ];

        $response = $this->makeRequest('POST', '/operation/pagemanipulation', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $content = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'manipulated.pdf',
            strlen($content)
        );
    }

    /**
     * Rotate pages in a PDF
     *
     * @param Document $document
     * @param int $angle 90, 180, or 270
     * @param array $pageRanges Optional page ranges
     * @return Document
     */
    public function rotatePages(Document $document, int $angle, array $pageRanges = [['start' => 1, 'end' => -1]]): Document
    {
        $asset = $this->uploadAsset(base64_decode($document->getContent()), $document->getMimeType());

        $requestData = [
            'assetID' => $asset['assetID'],
            'pageActions' => [
                [
                    'rotate' => [
                        'angle' => $angle,
                        'pageRanges' => $pageRanges
                    ]
                ]
            ]
        ];

        $response = $this->makeRequest('POST', '/operation/pagemanipulation', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $content = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'rotated.pdf',
            strlen($content)
        );
    }

    /**
     * Reorder pages in a PDF
     *
     * @param Document $document
     * @param array $pageRanges Sequence of page ranges for the new order
     * @return Document
     */
    public function reorderPages(Document $document, array $pageRanges): Document
    {
        $asset = $this->uploadAsset(base64_decode($document->getContent()), $document->getMimeType());

        $requestData = [
            'assetID' => $asset['assetID'],
            'pageActions' => [
                [
                    'reorder' => [
                        'pageRanges' => $pageRanges
                    ]
                ]
            ]
        ];

        $response = $this->makeRequest('POST', '/operation/pagemanipulation', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $content = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'reordered.pdf',
            strlen($content)
        );
    }
}
