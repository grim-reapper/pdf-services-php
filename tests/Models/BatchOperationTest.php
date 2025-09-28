<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Tests\Models;

use GrimReapper\PdfServices\Models\BatchOperation;
use PHPUnit\Framework\TestCase;

class BatchOperationTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $operationId = 'op-123';
        $type = 'convert';
        $input = ['file' => 'input.pdf'];

        $operation = new BatchOperation($operationId, $type, $input);

        $this->assertEquals($operationId, $operation->getOperationId());
        $this->assertEquals($type, $operation->getType());
        $this->assertEquals($input, $operation->getInput());
        $this->assertEquals([], $operation->getOutput());
        $this->assertEquals([], $operation->getOptions());
        $this->assertEquals('pending', $operation->getStatus());
        $this->assertNull($operation->getError());
        $this->assertTrue($operation->isPending());
        $this->assertFalse($operation->isCompleted());
        $this->assertFalse($operation->isFailed());
    }

    public function testConstructorWithAllParams(): void
    {
        $operationId = 'op-456';
        $type = 'convert';
        $input = ['file' => 'input.pdf'];
        $output = ['file' => 'output.txt'];
        $options = ['key' => 'value'];

        $operation = new BatchOperation($operationId, $type, $input, $output, $options);

        $this->assertEquals($operationId, $operation->getOperationId());
        $this->assertEquals($type, $operation->getType());
        $this->assertEquals($input, $operation->getInput());
        $this->assertEquals($output, $operation->getOutput());
        $this->assertEquals($options, $operation->getOptions());
        $this->assertEquals('pending', $operation->getStatus());
        $this->assertNull($operation->getError());
    }

    public function testUpdateStatusToCompleted(): void
    {
        $operation = new BatchOperation('op-test', 'convert', ['file' => 'input.pdf']);

        $updatedOperation = $operation->updateStatus('completed');

        $this->assertSame($operation, $updatedOperation);
        $this->assertEquals('completed', $operation->getStatus());
        $this->assertNull($operation->getError());
        $this->assertTrue($operation->isCompleted());
        $this->assertFalse($operation->isPending());
        $this->assertFalse($operation->isFailed());
    }

    public function testUpdateStatusToFailed(): void
    {
        $operation = new BatchOperation('op-test', 'convert', ['file' => 'input.pdf']);
        $error = 'Conversion failed';

        $operation->updateStatus('failed', $error);

        $this->assertEquals('failed', $operation->getStatus());
        $this->assertEquals($error, $operation->getError());
        $this->assertTrue($operation->isFailed());
        $this->assertFalse($operation->isPending());
        $this->assertFalse($operation->isCompleted());
    }

    public function testToArray(): void
    {
        $operationId = 'op-789';
        $type = 'convert';
        $input = ['file' => 'input.pdf', 'format' => 'pdf'];
        $output = ['file' => 'output.docx', 'format' => 'docx'];
        $options = ['option1' => 'value1'];

        $operation = new BatchOperation($operationId, $type, $input, $output, $options);

        $expectedArray = [
            'operationId' => $operationId,
            'type' => $type,
            'input' => $input,
            'output' => $output,
            'options' => $options,
        ];

        $this->assertEquals($expectedArray, $operation->toArray());
    }

    public function testFromArrayMinimal(): void
    {
        $data = [
            'operationId' => 'op-array-123',
            'type' => 'merge'
        ];

        $operation = BatchOperation::fromArray($data);

        $this->assertEquals('op-array-123', $operation->getOperationId());
        $this->assertEquals('merge', $operation->getType());
        $this->assertEquals([], $operation->getInput());
        $this->assertEquals([], $operation->getOutput());
        $this->assertEquals([], $operation->getOptions());
        $this->assertEquals('pending', $operation->getStatus());
        $this->assertNull($operation->getError());
    }

    public function testFromArrayComplete(): void
    {
        $data = [
            'operationId' => 'op-array-456',
            'type' => 'convert',
            'input' => ['file' => 'input.pdf'],
            'output' => ['file' => 'output.txt'],
            'options' => ['key' => 'value'],
            'status' => 'completed',
            'error' => 'none'
        ];

        $operation = BatchOperation::fromArray($data);

        $this->assertEquals('op-array-456', $operation->getOperationId());
        $this->assertEquals('convert', $operation->getType());
        $this->assertEquals(['file' => 'input.pdf'], $operation->getInput());
        $this->assertEquals(['file' => 'output.txt'], $operation->getOutput());
        $this->assertEquals(['key' => 'value'], $operation->getOptions());
        $this->assertEquals('completed', $operation->getStatus());
        $this->assertEquals('none', $operation->getError());
        $this->assertTrue($operation->isCompleted());
    }

    public function testCreateConversion(): void
    {
        $operation = BatchOperation::createConversion(
            'convert-op',
            'input.pdf',
            'output.docx',
            'pdf',
            'docx',
            ['quality' => 'high']
        );

        $this->assertEquals('convert-op', $operation->getOperationId());
        $this->assertEquals('convert', $operation->getType());
        $this->assertEquals(['file' => 'input.pdf', 'format' => 'pdf'], $operation->getInput());
        $this->assertEquals(['file' => 'output.docx', 'format' => 'docx'], $operation->getOutput());
        $this->assertEquals(['quality' => 'high'], $operation->getOptions());
    }

    public function testCreateMerge(): void
    {
        $inputFiles = ['file1.pdf', 'file2.pdf'];
        $operation = BatchOperation::createMerge(
            'merge-op',
            $inputFiles,
            'output.pdf',
            ['sort' => 'true']
        );

        $this->assertEquals('merge-op', $operation->getOperationId());
        $this->assertEquals('merge', $operation->getType());
        $this->assertEquals(['files' => $inputFiles], $operation->getInput());
        $this->assertEquals(['file' => 'output.pdf'], $operation->getOutput());
        $this->assertEquals(['sort' => 'true'], $operation->getOptions());
    }

    public function testCreateOcr(): void
    {
        $operation = BatchOperation::createOcr(
            'ocr-op',
            'input.pdf',
            'output.pdf',
            ['language' => 'en']
        );

        $this->assertEquals('ocr-op', $operation->getOperationId());
        $this->assertEquals('ocr', $operation->getType());
        $this->assertEquals(['file' => 'input.pdf'], $operation->getInput());
        $this->assertEquals(['file' => 'output.pdf'], $operation->getOutput());
        $this->assertEquals(['language' => 'en'], $operation->getOptions());
    }

    public function testIsPending(): void
    {
        $operation = new BatchOperation('op-test', 'convert', ['file' => 'input.pdf']);
        $this->assertTrue($operation->isPending());

        $operation->updateStatus('completed');
        $this->assertFalse($operation->isPending());
    }

    public function testIsCompleted(): void
    {
        $operation = new BatchOperation('op-test', 'convert', ['file' => 'input.pdf']);
        $this->assertFalse($operation->isCompleted());

        $operation->updateStatus('completed');
        $this->assertTrue($operation->isCompleted());
    }

    public function testIsFailed(): void
    {
        $operation = new BatchOperation('op-test', 'convert', ['file' => 'input.pdf']);
        $this->assertFalse($operation->isFailed());

        $operation->updateStatus('failed');
        $this->assertTrue($operation->isFailed());
    }
}
