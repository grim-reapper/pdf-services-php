<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Utils;

/**
 * Utility class for specifying page ranges in PDF operations
 */
class PageRanges
{
    private array $ranges = [];

    /**
     * Add a single page to the ranges
     *
     * @param int $page Page number (1-based)
     * @return self
     */
    public function addSinglePage(int $page): self
    {
        $this->ranges[] = ['start' => $page, 'end' => $page];
        return $this;
    }

    /**
     * Add a range of pages
     *
     * @param int $start Start page number (1-based)
     * @param int $end End page number (1-based, use -1 for until end)
     * @return self
     */
    public function addRange(int $start, int $end): self
    {
        $this->ranges[] = ['start' => $start, 'end' => $end];
        return $this;
    }

    /**
     * Add all pages from a specific page until the end
     *
     * @param int $start Start page number (1-based)
     * @return self
     */
    public function addAllFrom(int $start): self
    {
        $this->ranges[] = ['start' => $start, 'end' => -1];
        return $this;
    }

    /**
     * Get the page ranges as an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->ranges;
    }

    /**
     * Check if any ranges have been added
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->ranges);
    }
}
