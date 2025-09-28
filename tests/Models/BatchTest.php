<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Tests\Models;

use GrimReapper\PdfServices\Models\Batch;
use GrimReapper\PdfServices\Models\BatchOperation;
use PHPUnit\Framework\TestCase;
use DateTime;

class BatchTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $batchId = 'batch-123';
        $operations = [new BatchOperation('op1', 'combine', ['file' => 'input.pdf'])];

        $batch = new Batch($batchId, $operations);

        $this->assertEquals($batchId, $batch->getBatchId());
        $this->assertEquals($operations, $batch->getOperations());
        $this->assertEquals('pending', $batch->getStatus());
        $this->assertInstanceOf(DateTime::class, $batch->getCreatedAt());
        $this->assertNull($batch->getCompletedAt());
        $this->assertEquals([], $batch->getResults());
        $this->assertNull($batch->getError());
        $this->assertEquals(1, $batch->getOperationCount());
        $this->assertEquals(0, $batch->getCompletedCount());
        $this->assertEquals(0, $batch->getFailedCount());
        $this->assertTrue($batch->isPending());
        $this->assertFalse($batch->isProcessing());
        $this->assertFalse($batch->isCompleted());
        $this->assertFalse($batch->isFailed());
    }

    public function testConstructorWithStatus(): void
    {
        $batchId = 'batch-456';
        $operations = [new BatchOperation('op1', 'combine', ['file' => 'input.pdf'])];
        $status = 'processing';

        $batch = new Batch($batchId, $operations, $status);

        $this->assertEquals($batchId, $batch->getBatchId());
        $this->assertEquals($status, $batch->getStatus());
        $this->assertTrue($batch->isProcessing());
    }

    public function testGetOperationCount(): void
    {
        $operations = [
            new BatchOperation('op1', 'combine', ['file' => 'input.pdf']),
            new BatchOperation('op2', 'split', ['file' => 'input.pdf']),
            new BatchOperation('op3', 'extract', ['file' => 'input.pdf'])
        ];

        $batch = new Batch('batch-789', $operations);
        $this->assertEquals(3, $batch->getOperationCount());
    }

    public function testGetCompletedCount(): void
    {
        $operations = [new BatchOperation('op1', 'combine', ['file' => 'input.pdf'])];
        $batch = new Batch('batch-test', $operations);

        $results = [
            ['operationId' => 'op1', 'status' => 'completed']
        ];
        $batch->updateStatus('processing', $results);

        $this->assertEquals(1, $batch->getCompletedCount());
        $this->assertEquals(0, $batch->getFailedCount());
    }

    public function testGetFailedCount(): void
    {
        $operations = [new BatchOperation('op1', 'combine', ['file' => 'input.pdf'])];
        $batch = new Batch('batch-test', $operations);

        $results = [
            ['operationId' => 'op1', 'status' => 'failed']
        ];
        $batch->updateStatus('processing', $results);

        $this->assertEquals(0, $batch->getCompletedCount());
        $this->assertEquals(1, $batch->getFailedCount());
    }

    public function testUpdateStatusToCompleted(): void
    {
        $batch = new Batch('batch-test', []);
        $results = [['result' => 'data']];

        $updatedBatch = $batch->updateStatus('completed', $results, null);

        $this->assertSame($batch, $updatedBatch);
        $this->assertEquals('completed', $batch->getStatus());
        $this->assertEquals($results, $batch->getResults());
        $this->assertNull($batch->getError());
        $this->assertInstanceOf(DateTime::class, $batch->getCompletedAt());
        $this->assertTrue($batch->isCompleted());
    }

    public function testUpdateStatusToFailed(): void
    {
        $batch = new Batch('batch-test', []);
        $error = 'Batch failed';

        $batch->updateStatus('failed', null, $error);

        $this->assertEquals('failed', $batch->getStatus());
        $this->assertEquals($error, $batch->getError());
        $this->assertInstanceOf(DateTime::class, $batch->getCompletedAt());
        $this->assertTrue($batch->isFailed());
    }

    public function testFromApiResponseMinimal(): void
    {
        $response = [
            'batchId' => 'api-batch-123'
        ];

        $batch = Batch::fromApiResponse($response);

        $this->assertEquals('api-batch-123', $batch->getBatchId());
        $this->assertEquals([], $batch->getOperations());
        $this->assertEquals('pending', $batch->getStatus());
        $this->assertEquals([], $batch->getResults());
        $this->assertNull($batch->getError());
    }

    public function testFromApiResponseComplete(): void
    {
        $response = [
            'batchId' => 'api-batch-789',
            'status' => 'completed',
            'operations' => [
                ['id' => 'op1', 'type' => 'combine'],
                ['id' => 'op2', 'type' => 'split']
            ],
            'created' => '2023-01-01T10:00:00Z',
            'completed' => '2023-01-01T11:00:00Z',
            'results' => [
                ['operationId' => 'op1', 'status' => 'completed'],
                ['operationId' => 'op2', 'status' => 'completed']
            ]
        ];

        $batch = Batch::fromApiResponse($response);

        $this->assertEquals('api-batch-789', $batch->getBatchId());
        $this->assertEquals('completed', $batch->getStatus());
        $this->assertCount(2, $batch->getOperations());
        $this->assertCount(2, $batch->getResults());
        $this->assertEquals(2, $batch->getCompletedCount());
        $this->assertInstanceOf(DateTime::class, $batch->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $batch->getCompletedAt());
    }

    public function testIsPending(): void
    {
        $batch = new Batch('batch-test', []);
        $this->assertTrue($batch->isPending());

        $batch->updateStatus('processing');
        $this->assertFalse($batch->isPending());
    }

    public function testIsProcessing(): void
    {
        $batch = new Batch('batch-test', []);
        $this->assertFalse($batch->isProcessing());

        $batch->updateStatus('processing');
        $this->assertTrue($batch->isProcessing());
    }

    public function testIsCompleted(): void
    {
        $batch = new Batch('batch-test', []);
        $this->assertFalse($batch->isCompleted());

        $batch->updateStatus('completed');
        $this->assertTrue($batch->isCompleted());
    }

    public function testIsFailed(): void
    {
        $batch = new Batch('batch-test', []);
        $this->assertFalse($batch->isFailed());

        $batch->updateStatus('failed');
        $this->assertTrue($batch->isFailed());
    }
}
