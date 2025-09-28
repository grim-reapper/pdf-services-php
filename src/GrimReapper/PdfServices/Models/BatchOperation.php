<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Models;

/**
 * Represents a single operation within a batch
 */
class BatchOperation
{
    private string $operationId;
    private string $type;
    private array $input;
    private array $output;
    private array $options;
    private string $status;
    private ?string $error;

    /**
     * Create a new batch operation
     *
     * @param string $operationId The operation ID
     * @param string $type The operation type (convert, merge, ocr, etc.)
     * @param array $input The input parameters
     * @param array $output The output parameters
     * @param array $options Additional options
     */
    public function __construct(
        string $operationId,
        string $type,
        array $input,
        array $output = [],
        array $options = []
    ) {
        $this->operationId = $operationId;
        $this->type = $type;
        $this->input = $input;
        $this->output = $output;
        $this->options = $options;
        $this->status = 'pending';
        $this->error = null;
    }

    /**
     * Get the operation ID
     *
     * @return string
     */
    public function getOperationId(): string
    {
        return $this->operationId;
    }

    /**
     * Get the operation type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the input parameters
     *
     * @return array
     */
    public function getInput(): array
    {
        return $this->input;
    }

    /**
     * Get the output parameters
     *
     * @return array
     */
    public function getOutput(): array
    {
        return $this->output;
    }

    /**
     * Get the options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the operation status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Get the error message
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Check if the operation is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the operation is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the operation failed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Update operation status
     *
     * @param string $status The new status
     * @param string|null $error The error message
     * @return self
     */
    public function updateStatus(string $status, ?string $error = null): self
    {
        $this->status = $status;
        $this->error = $error;
        return $this;
    }

    /**
     * Convert to array for API requests
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'operationId' => $this->operationId,
            'type' => $this->type,
            'input' => $this->input,
            'output' => $this->output,
            'options' => $this->options,
        ];
    }

    /**
     * Create operation from array
     *
     * @param array $data The operation data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $operation = new self(
            $data['operationId'] ?? '',
            $data['type'] ?? '',
            $data['input'] ?? [],
            $data['output'] ?? [],
            $data['options'] ?? []
        );

        if (isset($data['status'])) {
            $operation->status = $data['status'];
        }

        if (isset($data['error'])) {
            $operation->error = $data['error'];
        }

        return $operation;
    }

    /**
     * Create a conversion operation
     *
     * @param string $operationId The operation ID
     * @param string $inputFile The input file path
     * @param string $outputFile The output file path
     * @param string $fromFormat The source format
     * @param string $toFormat The target format
     * @param array $options Additional options
     * @return self
     */
    public static function createConversion(
        string $operationId,
        string $inputFile,
        string $outputFile,
        string $fromFormat,
        string $toFormat,
        array $options = []
    ): self {
        return new self(
            $operationId,
            'convert',
            [
                'file' => $inputFile,
                'format' => $fromFormat
            ],
            [
                'file' => $outputFile,
                'format' => $toFormat
            ],
            $options
        );
    }

    /**
     * Create a merge operation
     *
     * @param string $operationId The operation ID
     * @param array $inputFiles Array of input file paths
     * @param string $outputFile The output file path
     * @param array $options Additional options
     * @return self
     */
    public static function createMerge(
        string $operationId,
        array $inputFiles,
        string $outputFile,
        array $options = []
    ): self {
        return new self(
            $operationId,
            'merge',
            ['files' => $inputFiles],
            ['file' => $outputFile],
            $options
        );
    }

    /**
     * Create an OCR operation
     *
     * @param string $operationId The operation ID
     * @param string $inputFile The input file path
     * @param string $outputFile The output file path
     * @param array $options Additional options
     * @return self
     */
    public static function createOcr(
        string $operationId,
        string $inputFile,
        string $outputFile,
        array $options = []
    ): self {
        return new self(
            $operationId,
            'ocr',
            ['file' => $inputFile],
            ['file' => $outputFile],
            $options
        );
    }
}
