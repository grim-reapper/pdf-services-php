<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Batch;
use GrimReapper\PdfServices\Models\BatchOperation;
use GrimReapper\PdfServices\Models\Document;

/**
 * Service for processing batches of PDF operations
 *
 * The Adobe PDF Services API does not have a native batch endpoint.
 * This service executes operations sequentially using individual API endpoints.
 */
class BatchProcessorService extends AbstractService
{
    /** @var array<string, string> Map of operation types to API endpoints */
    private const OPERATION_ENDPOINTS = [
        'convert'   => '/operation/createpdf',
        'compress'  => '/operation/compresspdf',
        'merge'     => '/operation/combinepdf',
        'ocr'       => '/operation/ocr',
        'export'    => '/operation/exportpdf',
        'linearize' => '/operation/linearizepdf',
        'protect'   => '/operation/protectpdf',
        'split'     => '/operation/splitpdf',
    ];

    /** @var array<string, string> Map file extensions to MIME types */
    private const MIME_TYPES = [
        'doc'   => 'application/msword',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'   => 'application/vnd.ms-excel',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt'   => 'application/vnd.ms-powerpoint',
        'pptx'  => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pdf'   => 'application/pdf',
        'txt'   => 'text/plain',
        'rtf'   => 'application/rtf',
        'png'   => 'image/png',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpeg',
        'gif'   => 'image/gif',
        'bmp'   => 'image/bmp',
        'tiff'  => 'image/tiff',
        'tif'   => 'image/tiff',
    ];

    public function getServiceName(): string
    {
        return 'batch-processor';
    }

    /**
     * Create a batch (stores operations for later execution)
     */
    public function createBatch(array $operations, array $options = []): Batch
    {
        $batchOperations = array_map(function ($operation) {
            if ($operation instanceof BatchOperation) {
                return $operation;
            }
            if (is_array($operation)) {
                return BatchOperation::fromArray($operation);
            }
            throw new \InvalidArgumentException('Invalid operation format');
        }, $operations);

        return new Batch(uniqid('batch_', true), $batchOperations, 'pending');
    }

    /**
     * Execute a batch by running each operation sequentially
     */
    public function executeBatch(Batch $batch, int $maxWaitTime = 300, int $pollInterval = 5): Batch
    {
        $batch->updateStatus('processing');
        $results = [];
        $allSucceeded = true;

        foreach ($batch->getOperations() as $index => $operation) {
            $opId = $operation->getOperationId() ?? "operation_{$index}";

            try {
                $result = $this->executeOperation($operation);
                $results[$opId] = [
                    'status' => 'completed',
                    'output' => $result,
                ];
            } catch (\Throwable $e) {
                $allSucceeded = false;
                $results[$opId] = [
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        $batch->updateStatus(
            $allSucceeded ? 'completed' : 'failed',
            $results,
            $allSucceeded ? null : 'One or more operations failed'
        );

        return $batch;
    }

    /**
     * Execute a single batch operation by calling the appropriate API endpoint
     */
    private function executeOperation(BatchOperation $operation): array
    {
        $type = $operation->getType();
        $endpoint = self::OPERATION_ENDPOINTS[$type] ?? null;

        if ($endpoint === null) {
            throw new \InvalidArgumentException("Unsupported operation type: {$type}");
        }

        // Upload input file(s)
        $input = $operation->getInput();
        // Input can be: ['file' => 'path'] or ['files' => ['path1', 'path2']]
        $inputFiles = $input['files'] ?? ($input['file'] ? [$input['file']] : []);
        $assetIds = [];

        foreach ($inputFiles as $file) {
            $content = is_file($file) ? file_get_contents($file) : null;
            if ($content === false) {
                throw new \RuntimeException("Cannot read file: {$file}");
            }
            // Detect the correct MIME type from the file extension
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $mediaType = self::MIME_TYPES[$ext] ?? 'application/octet-stream';
            $asset = $this->uploadAsset($content, $mediaType);
            $assetIds[] = $asset['assetID'];
        }

        // Build request based on operation type
        $requestData = $this->buildRequestData($type, $operation, $assetIds);

        // Execute operation
        $response = $this->makeRequest('POST', $endpoint, $requestData);
        $jobResult = $this->pollJob($response['location']);

        // Extract output
        $outputData = $this->getResultData($jobResult, ['asset', 'content', 'assets']);
        $outputContent = $this->httpClient->download($outputData['downloadUri']);

        $output = $operation->getOutput();
        $outputFile = $output['file'] ?? null;

        // Save to disk if output file was specified in the operation definition
        if ($outputFile) {
            $dir = dirname($outputFile);
            if (!is_dir($dir) && $dir !== '.') {
                mkdir($dir, 0755, true);
            }
            file_put_contents($outputFile, $outputContent);
        }

        return [
            'downloadUri' => $outputData['downloadUri'],
            'content' => $outputContent,
            'outputFile' => $outputFile,
        ];
    }

    /**
     * Build request data for a specific operation type
     */
    private function buildRequestData(string $type, BatchOperation $operation, array $assetIds): array
    {
        $options = $operation->getOptions();

        return match ($type) {
            'convert' => [
                'assetID' => $assetIds[0],
                'documentLanguage' => $options['documentLanguage'] ?? 'en-US',
            ],
            'compress' => [
                'assetID' => $assetIds[0],
                'compressionLevel' => $options['compressionLevel'] ?? 'MEDIUM',
            ],
            'merge' => [
                'assets' => array_map(fn($id) => ['assetID' => $id], $assetIds),
            ],
            'ocr' => [
                'assetID' => $assetIds[0],
                'ocrLang' => $options['ocrLang'] ?? 'en-US',
                'ocrType' => $options['ocrType'] ?? 'searchable_image',
            ],
            'export' => [
                'assetID' => $assetIds[0],
                'targetFormat' => $options['targetFormat'] ?? 'docx',
            ],
            'linearize' => [
                'assetID' => $assetIds[0],
            ],
            'protect' => [
                'assetID' => $assetIds[0],
                'passwordProtection' => ['ownerPassword' => $options['password'] ?? ''],
                'encryptionAlgorithm' => $options['encryptionAlgorithm'] ?? 'AES_256',
            ],
            'split' => [
                'assetID' => $assetIds[0],
                'splitoption' => $options['splitoption'] ?? ['pageRanges' => [['start' => 1, 'end' => 1]]],
            ],
            default => throw new \InvalidArgumentException("Unsupported operation type: {$type}"),
        };
    }

    /**
     * Create batch from simple operation definitions
     */
    public function createBatchFromDefinitions(array $operationDefs): Batch
    {
        $operations = [];

        foreach ($operationDefs as $i => $def) {
            $operationId = $def['operationId'] ?? "operation_{$i}";

            $operations[] = BatchOperation::fromArray([
                'operationId' => $operationId,
                'type' => $def['type'],
                'input' => ['file' => $def['input']],
                'output' => $def['output'] ? ['file' => $def['output']] : [],
                'options' => $def['options'] ?? [],
            ]);
        }

        return $this->createBatch($operations);
    }

    /**
     * Create and execute a batch in one call
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
     */
    public function getBatchResults(Batch $batch): array
    {
        if (!$batch->isCompleted()) {
            throw new \RuntimeException('Batch is not completed yet');
        }

        $results = [];
        foreach ($batch->getResults() as $operationId => $result) {
            if (isset($result['output']['content']) && $result['output']['content']) {
                $outputFile = $result['output']['outputFile'] ?? "output_{$operationId}.pdf";
                $results[$operationId] = new Document(
                    $result['output']['content'],
                    'application/pdf',
                    basename($outputFile),
                    strlen($result['output']['content'])
                );
            }
        }

        return $results;
    }

    public function getBatchStatus(string $batchId): Batch
    {
        throw new \RuntimeException('Batch status polling is not supported — operations execute sequentially.');
    }

    public function cancelBatch(string $batchId): bool
    {
        throw new \RuntimeException('Batch cancellation is not supported.');
    }
}
