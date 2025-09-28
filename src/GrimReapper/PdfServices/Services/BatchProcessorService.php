<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Batch;
use GrimReapper\PdfServices\Models\BatchOperation;
use GrimReapper\PdfServices\Models\Document;

/**
 * Service for processing batches of PDF operations
 */
class BatchProcessorService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'batch-processor';
    }

    /**
     * Create a new batch with operations
     *
     * @param array $operations Array of BatchOperation objects or operation configs
     * @param array $options Batch options
     * @return Batch The created batch
     */
    public function createBatch(array $operations, array $options = []): Batch
    {
        // Convert operation configs to BatchOperation objects if needed
        $batchOperations = array_map(function ($operation) {
            if ($operation instanceof BatchOperation) {
                return $operation;
            }

            if (is_array($operation)) {
                return BatchOperation::fromArray($operation);
            }

            throw new \InvalidArgumentException('Invalid operation format');
        }, $operations);

        $batchData = [
            'operations' => array_map(fn($op) => $op->toArray(), $batchOperations),
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/operation/batch', $batchData);

        return Batch::fromApiResponse($response);
    }

    /**
     * Get batch status
     *
     * @param string $batchId The batch ID
     * @return Batch The batch with updated status
     */
    public function getBatchStatus(string $batchId): Batch
    {
        $response = $this->makeRequest('GET', "/operation/batch/{$batchId}");

        return Batch::fromApiResponse($response);
    }

    /**
     * Cancel a batch
     *
     * @param string $batchId The batch ID
     * @return bool True if cancelled successfully
     */
    public function cancelBatch(string $batchId): bool
    {
        $response = $this->makeRequest('DELETE', "/operation/batch/{$batchId}");

        return isset($response['cancelled']) && $response['cancelled'] === true;
    }

    /**
     * Execute batch synchronously (wait for completion)
     *
     * @param Batch $batch The batch to execute
     * @param int $maxWaitTime Maximum wait time in seconds (default 300)
     * @param int $pollInterval Poll interval in seconds (default 5)
     * @return Batch The completed batch
     */
    public function executeBatch(
        Batch $batch,
        int $maxWaitTime = 300,
        int $pollInterval = 5
    ): Batch {
        $startTime = time();

        while (!$batch->isCompleted() && !$batch->isFailed()) {
            if (time() - $startTime > $maxWaitTime) {
                throw new \RuntimeException("Batch execution timed out after {$maxWaitTime} seconds");
            }

            sleep($pollInterval);
            $batch = $this->getBatchStatus($batch->getBatchId());
        }

        return $batch;
    }

    /**
     * Create and execute a batch in one call
     *
     * @param array $operations Array of operations
     * @param array $options Batch options
     * @param int $maxWaitTime Maximum wait time in seconds
     * @param int $pollInterval Poll interval in seconds
     * @return Batch The completed batch
     */
    public function createAndExecuteBatch(
        array $operations,
        array $options = [],
        int $maxWaitTime = 300,
        int $pollInterval = 5
    ): Batch {
        $batch = $this->createBatch($operations, $options);
        return $this->executeBatch($batch, $maxWaitTime, $pollInterval);
    }

    /**
     * Get batch results as documents
     *
     * @param Batch $batch The completed batch
     * @return array Array of Document objects keyed by operation ID
     */
    public function getBatchResults(Batch $batch): array
    {
        if (!$batch->isCompleted()) {
            throw new \RuntimeException('Batch is not completed yet');
        }

        $results = [];
        foreach ($batch->getResults() as $operationId => $result) {
            if (isset($result['output']) && isset($result['output']['file'])) {
                // Download the result file
                $outputPath = $result['output']['file'];
                $document = $this->downloadResult($outputPath);
                $results[$operationId] = $document;
            }
        }

        return $results;
    }

    /**
     * Download a result file
     *
     * @param string $resultUrl The result URL
     * @return Document The downloaded document
     */
    private function downloadResult(string $resultUrl): Document
    {
        // This would typically download from the result URL
        // For now, we'll simulate with a placeholder
        return Document::fromString('Downloaded content', 'application/pdf', basename($resultUrl));
    }

    /**
     * Create batch from simple operation definitions
     *
     * @param array $operationDefs Array of operation definitions
     * @return Batch The created batch
     *
     * Example:
     * [
     *     ['type' => 'convert', 'input' => 'doc1.docx', 'output' => 'pdf1.pdf'],
     *     ['type' => 'merge', 'inputs' => ['pdf1.pdf', 'pdf2.pdf'], 'output' => 'merged.pdf'],
     *     ['type' => 'ocr', 'input' => 'scanned.pdf', 'output' => 'ocr.pdf']
     * ]
     */
    public function createBatchFromDefinitions(array $operationDefs): Batch
    {
        $operations = [];

        foreach ($operationDefs as $i => $def) {
            $operationId = $def['operationId'] ?? "operation_{$i}";

            switch ($def['type']) {
                case 'convert':
                    $operations[] = BatchOperation::createConversion(
                        $operationId,
                        $def['input'],
                        $def['output'],
                        $def['fromFormat'] ?? $this->guessFormat($def['input']),
                        $def['toFormat'] ?? $this->guessFormat($def['output']),
                        $def['options'] ?? []
                    );
                    break;

                case 'merge':
                    $operations[] = BatchOperation::createMerge(
                        $operationId,
                        $def['inputs'],
                        $def['output'],
                        $def['options'] ?? []
                    );
                    break;

                case 'ocr':
                    $operations[] = BatchOperation::createOcr(
                        $operationId,
                        $def['input'],
                        $def['output'],
                        $def['options'] ?? []
                    );
                    break;

                default:
                    throw new \InvalidArgumentException("Unsupported operation type: {$def['type']}");
            }
        }

        return $this->createBatch($operations);
    }

    /**
     * Guess file format from extension
     *
     * @param string $filename The filename
     * @return string The format
     */
    private function guessFormat(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        $formats = [
            'pdf' => 'pdf',
            'docx' => 'docx',
            'doc' => 'doc',
            'xlsx' => 'xlsx',
            'xls' => 'xls',
            'pptx' => 'pptx',
            'ppt' => 'ppt',
            'jpg' => 'jpg',
            'jpeg' => 'jpg',
            'png' => 'png',
            'gif' => 'gif',
            'tiff' => 'tiff',
            'tif' => 'tiff',
        ];

        return $formats[$extension] ?? 'pdf';
    }
}
