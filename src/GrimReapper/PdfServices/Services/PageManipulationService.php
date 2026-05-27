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
            ],
            'json' => '{}'
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
            ],
            'json' => '{}'
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
            'assets' => [
                [
                    'assetID' => $asset['assetID'],
                    'pageRanges' => $pageRanges
                ]
            ],
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/combinepdf', $requestData);
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

    /**
     * Insert pages from a source document into a base document
     *
     * @param Document $baseDocument The document to insert into
     * @param Document $sourceDocument The document to insert from
     * @param int $atPage The page number in the base document to insert after (0 for beginning)
     * @param array $pageRanges Page ranges from the source document
     * @return Document
     */
    public function insertPages(Document $baseDocument, Document $sourceDocument, int $atPage, ?array $pageRanges = null): Document
    {
        $baseAsset = $this->uploadAsset(base64_decode($baseDocument->getContent()), $baseDocument->getMimeType());
        $sourceAsset = $this->uploadAsset(base64_decode($sourceDocument->getContent()), $sourceDocument->getMimeType());

        $metadataService = new MetadataService($this->config, $this->httpClient);
        $metadata = $metadataService->getMetadataFromDocument($baseDocument);
        $totalPages = $metadata['document']['pageCount'] ?? $metadata['pageCount'] ?? 0;

        $assets = [];

        // 1. Pages before insertion point
        if ($atPage > 0) {
            $assets[] = [
                'assetID' => $baseAsset['assetID'],
                'pageRanges' => [['start' => 1, 'end' => min($atPage, $totalPages)]]
            ];
        }

        // 2. Inserted pages
        $insertedAsset = ['assetID' => $sourceAsset['assetID']];
        if ($pageRanges) {
            $insertedAsset['pageRanges'] = $pageRanges;
        }
        $assets[] = $insertedAsset;

        // 3. Pages after insertion point
        if ($atPage < $totalPages && $atPage >= 0) {
            $assets[] = [
                'assetID' => $baseAsset['assetID'],
                'pageRanges' => [['start' => $atPage + 1, 'end' => $totalPages]]
            ];
        }

        $requestData = [
            'assets' => $assets,
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/combinepdf', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $content = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'inserted.pdf',
            strlen($content)
        );
    }

    /**
     * Replace pages in a base document with pages from a source document
     *
     * @param Document $baseDocument The document to replace pages in
     * @param array $basePageRanges Page ranges in the base document to be replaced (currently supporting single range for simplicity)
     * @param Document $sourceDocument The document to take replacement pages from
     * @param array|null $sourcePageRanges Page ranges from the source document
     * @return Document
     */
    public function replacePages(Document $baseDocument, array $basePageRanges, Document $sourceDocument, ?array $sourcePageRanges = null): Document
    {
        $baseAsset = $this->uploadAsset(base64_decode($baseDocument->getContent()), $baseDocument->getMimeType());
        $sourceAsset = $this->uploadAsset(base64_decode($sourceDocument->getContent()), $sourceDocument->getMimeType());

        $metadataService = new MetadataService($this->config, $this->httpClient);
        $metadata = $metadataService->getMetadataFromDocument($baseDocument);
        $totalPages = $metadata['document']['pageCount'] ?? $metadata['pageCount'] ?? 0;

        // Assuming first range for replacement logic
        $replaceRange = $basePageRanges[0] ?? ['start' => 1, 'end' => 1];
        $replaceStart = (int)$replaceRange['start'];
        $replaceEnd = (int)$replaceRange['end'];

        $assets = [];

        // 1. Pages before replaced range
        if ($replaceStart > 1) {
            $assets[] = [
                'assetID' => $baseAsset['assetID'],
                'pageRanges' => [['start' => 1, 'end' => $replaceStart - 1]]
            ];
        }

        // 2. Replacement pages
        $replacementAsset = ['assetID' => $sourceAsset['assetID']];
        if ($sourcePageRanges) {
            $replacementAsset['pageRanges'] = $sourcePageRanges;
        }
        $assets[] = $replacementAsset;

        // 3. Pages after replaced range
        if ($replaceEnd < $totalPages && $replaceEnd > 0) {
            $assets[] = [
                'assetID' => $baseAsset['assetID'],
                'pageRanges' => [['start' => $replaceEnd + 1, 'end' => $totalPages]]
            ];
        }

        $requestData = [
            'assets' => $assets,
            'json' => '{}'
        ];

        $response = $this->makeRequest('POST', '/operation/combinepdf', $requestData);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $content = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'replaced.pdf',
            strlen($content)
        );
    }
}
