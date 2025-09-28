<?php

declare(strict_types=1);

/**
 * Batch Processing Example for Adobe PDF Services PHP SDK
 *
 * This example demonstrates how to:
 * - Create and execute batch operations
 * - Process multiple files efficiently
 * - Monitor batch progress
 * - Handle batch results
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use GrimReapper\PdfServices\Client;
use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Models\BatchOperation;
use GrimReapper\PdfServices\Exceptions\PdfServicesException;

function main(): void
{
    try {
        // Configuration
        $config = new PdfServicesConfig(
            apiKey: getenv('ADOBE_PDF_SERVICES_API_KEY') ?: 'your-api-key',
            clientId: getenv('ADOBE_PDF_SERVICES_CLIENT_ID') ?: 'your-client-id',
            organizationId: getenv('ADOBE_PDF_SERVICES_ORGANIZATION_ID') ?: 'your-organization-id'
        );

        // Create the client
        $client = new Client($config);
        $batchService = $client->batch();

        echo "Adobe PDF Services PHP SDK - Batch Processing Example\n";
        echo "=====================================================\n\n";

        // Example 1: Create batch from operation definitions (simple approach)
        echo "1. Creating batch from operation definitions...\n";

        $operationDefs = [
            [
                'type' => 'convert',
                'input' => 'input/document1.docx',
                'output' => 'output/document1.pdf'
            ],
            [
                'type' => 'convert',
                'input' => 'input/document2.docx',
                'output' => 'output/document2.pdf'
            ],
            [
                'type' => 'merge',
                'inputs' => ['output/document1.pdf', 'output/document2.pdf'],
                'output' => 'output/merged.pdf'
            ],
            [
                'type' => 'ocr',
                'input' => 'input/scanned.pdf',
                'output' => 'output/ocr.pdf'
            ]
        ];

        $batch = $batchService->createBatchFromDefinitions($operationDefs);
        echo "✓ Batch created with ID: {$batch->getBatchId()}\n";
        echo "  Operations: {$batch->getOperationCount()}\n\n";

        // Example 2: Create batch using BatchOperation objects (advanced approach)
        echo "2. Creating batch with BatchOperation objects...\n";

        $operations = [
            BatchOperation::createConversion(
                'conv-1',
                'input/report.docx',
                'output/report.pdf',
                'docx',
                'pdf'
            ),
            BatchOperation::createConversion(
                'conv-2',
                'input/presentation.pptx',
                'output/presentation.pdf',
                'pptx',
                'pdf'
            ),
            BatchOperation::createMerge(
                'merge-1',
                ['output/report.pdf', 'output/presentation.pdf'],
                'output/combined.pdf'
            )
        ];

        $batch2 = $batchService->createBatch($operations);
        echo "✓ Advanced batch created with ID: {$batch2->getBatchId()}\n\n";

        // Example 3: Monitor batch progress
        echo "3. Monitoring batch execution...\n";

        // For demonstration, we'll simulate monitoring the first batch
        // In a real scenario, you would check if files exist first
        echo "  Batch Status: {$batch->getStatus()}\n";
        echo "  Operations: {$batch->getOperationCount()}\n";
        echo "  Completed: {$batch->getCompletedCount()}\n";
        echo "  Failed: {$batch->getFailedCount()}\n\n";

        // Example 4: Synchronous batch execution (wait for completion)
        echo "4. Synchronous batch execution example...\n";

        // Create a small batch for demonstration
        $syncOperations = [
            [
                'type' => 'convert',
                'input' => 'input/sample.docx',
                'output' => 'output/sample.pdf'
            ]
        ];

        try {
            $syncBatch = $batchService->createBatchFromDefinitions($syncOperations);
            echo "✓ Synchronous batch created: {$syncBatch->getBatchId()}\n";

            // Execute synchronously (would wait for completion in real API)
            // $completedBatch = $batchService->executeBatch($syncBatch, 60, 2);
            // echo "✓ Batch completed: {$completedBatch->getStatus()}\n";

            echo "  (Note: Actual execution requires valid input files)\n\n";
        } catch (Exception $e) {
            echo "  Batch execution skipped (no valid input files): {$e->getMessage()}\n\n";
        }

        // Example 5: Batch result handling
        echo "5. Batch result handling...\n";

        // Simulate completed batch results
        $mockResults = [
            'conv-1' => ['status' => 'completed', 'output' => ['file' => 'output/report.pdf']],
            'conv-2' => ['status' => 'completed', 'output' => ['file' => 'output/presentation.pdf']],
            'merge-1' => ['status' => 'completed', 'output' => ['file' => 'output/combined.pdf']]
        ];

        $batch2->updateStatus('completed', $mockResults);

        echo "✓ Batch completed with {$batch2->getCompletedCount()} successful operations\n";

        // Get results as documents
        try {
            $results = $batchService->getBatchResults($batch2);
            echo "  Retrieved {$batch2->getCompletedCount()} result documents\n";

            foreach ($results as $operationId => $document) {
                echo "    {$operationId}: {$document->getFilename()}\n";
            }
        } catch (Exception $e) {
            echo "  Result retrieval skipped: {$e->getMessage()}\n";
        }

        echo "\n";

        // Example 6: Error handling and batch cancellation
        echo "6. Error handling and batch management...\n";

        // Check batch status
        $status = $batchService->getBatchStatus($batch->getBatchId());
        echo "✓ Batch status checked: {$status->getStatus()}\n";

        // Cancel batch if needed
        // $cancelled = $batchService->cancelBatch($batch->getBatchId());
        // echo "✓ Batch cancelled: " . ($cancelled ? 'Yes' : 'No') . "\n";

        echo "  (Note: Cancellation would work on running batches)\n\n";

        echo "Batch processing examples completed!\n";
        echo "Key benefits of batch processing:\n";
        echo "• Process multiple operations efficiently\n";
        echo "• Monitor progress and handle errors gracefully\n";
        echo "• Reduce API calls and improve performance\n";
        echo "• Support for complex document workflows\n";

    } catch (PdfServicesException $e) {
        echo "PDF Services Error: " . $e->getMessage() . "\n";
        if ($e->getRequestId()) {
            echo "Request ID: " . $e->getRequestId() . "\n";
        }
        exit(1);
    } catch (Exception $e) {
        echo "General Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Ensure output directory exists
if (!is_dir('output')) {
    mkdir('output', 0755, true);
}
if (!is_dir('input')) {
    mkdir('input', 0755, true);
}

// Run the example
main();
