<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Models;

/**
 * Represents the result of a PDF comparison operation
 */
class ComparisonResult
{
    private bool $identical;
    private int $differenceCount;
    private array $differences;
    private array $visualDifferences;
    private array $textDifferences;

    /**
     * Create a new comparison result
     *
     * @param bool $identical Whether the documents are identical
     * @param int $differenceCount Number of differences found
     * @param array $differences List of all differences
     * @param array $visualDifferences Visual differences
     * @param array $textDifferences Text differences
     */
    public function __construct(
        bool $identical = true,
        int $differenceCount = 0,
        array $differences = [],
        array $visualDifferences = [],
        array $textDifferences = []
    ) {
        $this->identical = $identical;
        $this->differenceCount = $differenceCount;
        $this->differences = $differences;
        $this->visualDifferences = $visualDifferences;
        $this->textDifferences = $textDifferences;
    }

    /**
     * Check if the documents are identical
     *
     * @return bool
     */
    public function areIdentical(): bool
    {
        return $this->identical;
    }

    /**
     * Get the number of differences
     *
     * @return int
     */
    public function getDifferenceCount(): int
    {
        return $this->differenceCount;
    }

    /**
     * Get all differences
     *
     * @return array
     */
    public function getDifferences(): array
    {
        return $this->differences;
    }

    /**
     * Get visual differences
     *
     * @return array
     */
    public function getVisualDifferences(): array
    {
        return $this->visualDifferences;
    }

    /**
     * Get text differences
     *
     * @return array
     */
    public function getTextDifferences(): array
    {
        return $this->textDifferences;
    }

    /**
     * Add a difference
     *
     * @param Comparison $difference The difference to add
     * @return self
     */
    public function addDifference(Comparison $difference): self
    {
        $this->differences[] = $difference;
        $this->differenceCount++;
        $this->identical = false;

        // Categorize the difference
        switch ($difference->getType()) {
            case 'visual':
                $this->visualDifferences[] = $difference;
                break;
            case 'text':
                $this->textDifferences[] = $difference;
                break;
        }

        return $this;
    }

    /**
     * Create comparison result from API response
     *
     * @param array $response The API response data
     * @return self
     */
    public static function fromApiResponse(array $response): self
    {
        $result = new self(
            $response['identical'] ?? true,
            $response['differenceCount'] ?? 0
        );

        if (isset($response['differences']) && is_array($response['differences'])) {
            foreach ($response['differences'] as $diff) {
                $result->addDifference(Comparison::fromApiResponse($diff));
            }
        }

        return $result;
    }
}
