<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Models;

use GrimReapper\PdfServices\Exceptions\ValidationException;

/**
 * Represents a PDF document
 */
class Document
{
    private string $content;
    private string $mimeType;
    private ?string $filename;
    private ?int $size;

    /**
     * Create a new document
     *
     * @param string $content The document content (base64 encoded for binary)
     * @param string $mimeType The MIME type
     * @param string|null $filename The filename
     * @param int|null $size The file size in bytes
     */
    public function __construct(
        string $content,
        string $mimeType = 'application/pdf',
        ?string $filename = null,
        ?int $size = null
    ) {
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->filename = $filename;
        $this->size = $size;
    }

    /**
     * Get the document content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Get the MIME type
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Get the filename
     *
     * @return string|null
     */
    public function getFilename(): ?string
    {
        return $this->filename;
    }

    /**
     * Get the file size
     *
     * @return int|null
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * Check if this is a PDF document
     *
     * @return bool
     */
    public function isPdf(): bool
    {
        return $this->mimeType === 'application/pdf';
    }

    /**
     * Save the document to a file
     *
     * @param string $path The file path to save to
     * @return bool
     * @throws ValidationException
     */
    public function saveTo(string $path): bool
    {
        if (empty($path)) {
            throw new ValidationException('File path cannot be empty');
        }

        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new ValidationException("Cannot create directory: {$directory}");
        }

        $content = $this->isBinaryContent() ? base64_decode($this->content) : $this->content;

        return file_put_contents($path, $content) !== false;
    }

    /**
     * Check if content is binary (base64 encoded)
     *
     * @return bool
     */
    private function isBinaryContent(): bool
    {
        return $this->mimeType !== 'text/html' && $this->mimeType !== 'text/plain';
    }

    /**
     * Create document from file
     *
     * @param string $filePath The file path
     * @return self
     * @throws ValidationException
     */
    public static function fromFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new ValidationException("File does not exist: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new ValidationException("File is not readable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new ValidationException("Failed to read file: {$filePath}");
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $size = filesize($filePath);
        $filename = basename($filePath);

        // Base64 encode binary content
        if ($mimeType !== 'text/html' && $mimeType !== 'text/plain') {
            $content = base64_encode($content);
        }

        return new self($content, $mimeType, $filename, $size);
    }

    /**
     * Create document from string content
     *
     * @param string $content The content
     * @param string $mimeType The MIME type
     * @param string|null $filename The filename
     * @return self
     */
    public static function fromString(
        string $content,
        string $mimeType = 'text/plain',
        ?string $filename = null
    ): self {
        $size = strlen($content);
        return new self($content, $mimeType, $filename, $size);
    }
}
