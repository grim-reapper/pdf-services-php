<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Models;

use DateTime;
use DateTimeInterface;

/**
 * Represents a batch of PDF operations
 */
class Batch
{
    private string $batchId;
    private array $operations;
    private string $status;
    private ?DateTimeInterface $createdAt;
    private ?DateTimeInterface $completedAt;
    private array $results;
    private ?string $error;

    /**
     * Create a new batch
     *
     * @param string $batchId The batch ID
     * @param array $operations Array of BatchOperation objects
     * @param string $status The batch status
     */
    public function __construct(
        string $batchId,
        array $operations,
        string $status = 'pending'
    ) {
        $this->batchId = $batchId;
        $this->operations = $operations;
        $this->status = $status;
        $this->createdAt = new DateTime();
        $this->completedAt = null;
        $this->results = [];
        $this->error = null;
    }

    /**
     * Get the batch ID
     *
     * @return string
     */
    public function getBatchId(): string
    {
        return $this->batchId;
    }

    /**
     * Get the operations
     *
     * @return array
     */
    public function getOperations(): array
    {
        return $this->operations;
    }

    /**
     * Get the batch status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Check if the batch is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the batch is processing
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if the batch is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the batch failed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get when the batch was created
     *
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Get when the batch was completed
     *
     * @return DateTimeInterface|null
     */
    public function getCompletedAt(): ?DateTimeInterface
    {
        return $this->completedAt;
    }

    /**
     * Get the results
     *
     * @return array
     */
    public function getResults(): array
    {
        return $this->results;
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
     * Get operation count
     *
     * @return int
     */
    public function getOperationCount(): int
    {
        return count($this->operations);
    }

    /**
     * Get completed operation count
     *
     * @return int
     */
    public function getCompletedCount(): int
    {
        return count(array_filter($this->results, fn($result) => isset($result['status']) && $result['status'] === 'completed'));
    }

    /**
     * Get failed operation count
     *
     * @return int
     */
    public function getFailedCount(): int
    {
        return count(array_filter($this->results, fn($result) => isset($result['status']) && $result['status'] === 'failed'));
    }

    /**
     * Update batch status
     *
     * @param string $status The new status
     * @param array|null $results The results array
     * @param string|null $error The error message
     * @return self
     */
    public function updateStatus(string $status, ?array $results = null, ?string $error = null): self
    {
        $this->status = $status;

        if ($results !== null) {
            $this->results = $results;
        }

        if ($error !== null) {
            $this->error = $error;
        }

        if (in_array($status, ['completed', 'failed'])) {
            $this->completedAt = new DateTime();
        }

        return $this;
    }

    /**
     * Create batch from API response
     *
     * @param array $response The API response data
     * @return self
     */
    public static function fromApiResponse(array $response): self
    {
        $operations = array_map(
            fn($op) => BatchOperation::fromArray($op),
            $response['operations'] ?? []
        );

        $batch = new self(
            $response['batchId'] ?? '',
            $operations,
            $response['status'] ?? 'pending'
        );

        if (isset($response['created'])) {
            $batch->createdAt = new DateTime($response['created']);
        }

        if (isset($response['completed'])) {
            $batch->completedAt = new DateTime($response['completed']);
        }

        $batch->results = $response['results'] ?? [];
        $batch->error = $response['error'] ?? null;

        return $batch;
    }
}
