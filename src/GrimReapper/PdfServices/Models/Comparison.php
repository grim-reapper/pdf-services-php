<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Models;

/**
 * Represents a single difference found during PDF comparison
 */
class Comparison
{
    private string $type;
    private int $pageNumber;
    private string $description;
    private array $position;
    private ?string $oldValue;
    private ?string $newValue;

    /**
     * Create a new comparison difference
     *
     * @param string $type The type of difference ('text', 'visual', 'structural')
     * @param int $pageNumber The page number where the difference was found
     * @param string $description Description of the difference
     * @param array $position Position coordinates [x, y] or [x, y, width, height]
     * @param string|null $oldValue The old value (for text differences)
     * @param string|null $newValue The new value (for text differences)
     */
    public function __construct(
        string $type,
        int $pageNumber,
        string $description,
        array $position = [],
        ?string $oldValue = null,
        ?string $newValue = null
    ) {
        $this->type = $type;
        $this->pageNumber = $pageNumber;
        $this->description = $description;
        $this->position = $position;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * Get the difference type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get the page number
     *
     * @return int
     */
    public function getPageNumber(): int
    {
        return $this->pageNumber;
    }

    /**
     * Get the description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the position
     *
     * @return array
     */
    public function getPosition(): array
    {
        return $this->position;
    }

    /**
     * Get the old value
     *
     * @return string|null
     */
    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    /**
     * Get the new value
     *
     * @return string|null
     */
    public function getNewValue(): ?string
    {
        return $this->newValue;
    }

    /**
     * Check if this is a text difference
     *
     * @return bool
     */
    public function isTextDifference(): bool
    {
        return $this->type === 'text';
    }

    /**
     * Check if this is a visual difference
     *
     * @return bool
     */
    public function isVisualDifference(): bool
    {
        return $this->type === 'visual';
    }

    /**
     * Check if this is a structural difference
     *
     * @return bool
     */
    public function isStructuralDifference(): bool
    {
        return $this->type === 'structural';
    }

    /**
     * Create comparison from API response
     *
     * @param array $response The API response data
     * @return self
     */
    public static function fromApiResponse(array $response): self
    {
        return new self(
            $response['type'] ?? 'unknown',
            $response['pageNumber'] ?? 1,
            $response['description'] ?? '',
            $response['position'] ?? [],
            $response['oldValue'] ?? null,
            $response['newValue'] ?? null
        );
    }
}
