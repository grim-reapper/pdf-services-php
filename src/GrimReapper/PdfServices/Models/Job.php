<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Models;

use DateTime;
use DateTimeInterface;

/**
 * Represents a PDF Services job
 */
class Job
{
    private string $jobId;
    private string $status;
    private ?DateTimeInterface $createdAt;
    private ?DateTimeInterface $updatedAt;
    private ?array $result;
    private ?string $error;

    /**
     * Create a new job
     *
     * @param string $jobId The job ID
     * @param string $status The job status
     * @param DateTimeInterface|null $createdAt When the job was created
     * @param DateTimeInterface|null $updatedAt When the job was last updated
     * @param array|null $result The job result
     * @param string|null $error The error message if failed
     */
    public function __construct(
        string $jobId,
        string $status = 'in_progress',
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
        ?array $result = null,
        ?string $error = null
    ) {
        $this->jobId = $jobId;
        $this->status = $status;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
        $this->result = $result;
        $this->error = $error;
    }

    /**
     * Get the job ID
     *
     * @return string
     */
    public function getJobId(): string
    {
        return $this->jobId;
    }

    /**
     * Get the job status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Check if the job is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'done';
    }

    /**
     * Check if the job is in progress
     *
     * @return bool
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if the job failed
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get when the job was created
     *
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * Get when the job was last updated
     *
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * Get the job result
     *
     * @return array|null
     */
    public function getResult(): ?array
    {
        return $this->result;
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
     * Update the job status
     *
     * @param string $status The new status
     * @param array|null $result The result data
     * @param string|null $error The error message
     * @return self
     */
    public function updateStatus(string $status, ?array $result = null, ?string $error = null): self
    {
        $this->status = $status;
        $this->updatedAt = new DateTime();
        $this->result = $result;
        $this->error = $error;

        return $this;
    }

    /**
     * Create job from API response
     *
     * @param array $response The API response
     * @return self
     */
    public static function fromApiResponse(array $response): self
    {
        $createdAt = isset($response['created']) ? new DateTime($response['created']) : null;
        $updatedAt = isset($response['modified']) ? new DateTime($response['modified']) : null;

        return new self(
            $response['jobId'] ?? '',
            $response['status'] ?? 'in_progress',
            $createdAt,
            $updatedAt,
            $response['result'] ?? null,
            $response['error'] ?? null
        );
    }
}
