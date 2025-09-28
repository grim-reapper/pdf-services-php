<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Utils;

use GrimReapper\PdfServices\Exceptions\ValidationException;

/**
 * Utility class for file operations
 */
class FileUtils
{
    /**
     * Get the MIME type of a file
     *
     * @param string $filePath The file path
     * @return string The MIME type
     * @throws ValidationException
     */
    public static function getMimeType(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new ValidationException("File does not exist: {$filePath}");
        }

        $mimeType = mime_content_type($filePath);

        if ($mimeType === false) {
            // Fallback for systems without mime_content_type
            $mimeType = self::guessMimeTypeFromExtension($filePath);
        }

        return $mimeType ?: 'application/octet-stream';
    }

    /**
     * Guess MIME type from file extension
     *
     * @param string $filePath The file path
     * @return string The guessed MIME type
     */
    private static function guessMimeTypeFromExtension(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'htm' => 'text/html',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Validate file size
     *
     * @param string $filePath The file path
     * @param int $maxSizeBytes Maximum size in bytes (default 100MB)
     * @throws ValidationException
     */
    public static function validateFileSize(string $filePath, int $maxSizeBytes = 104857600): void
    {
        $size = filesize($filePath);

        if ($size === false) {
            throw new ValidationException("Cannot determine file size: {$filePath}");
        }

        if ($size > $maxSizeBytes) {
            $maxSizeMB = round($maxSizeBytes / 1048576, 2);
            throw new ValidationException("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }
    }

    /**
     * Check if file is readable
     *
     * @param string $filePath The file path
     * @throws ValidationException
     */
    public static function validateFileReadable(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new ValidationException("File does not exist: {$filePath}");
        }

        if (!is_readable($filePath)) {
            throw new ValidationException("File is not readable: {$filePath}");
        }

        if (!is_file($filePath)) {
            throw new ValidationException("Path is not a file: {$filePath}");
        }
    }

    /**
     * Create a temporary file with the given content
     *
     * @param string $content The file content
     * @param string $prefix The filename prefix
     * @param string $extension The file extension
     * @return string The temporary file path
     * @throws ValidationException
     */
    public static function createTempFile(
        string $content,
        string $prefix = 'adobe_pdf_',
        string $extension = 'tmp'
    ): string {
        $tempFile = tempnam(sys_get_temp_dir(), $prefix);

        if ($tempFile === false) {
            throw new ValidationException('Failed to create temporary file');
        }

        // Add extension if provided
        if ($extension && !str_ends_with($tempFile, ".{$extension}")) {
            $newPath = $tempFile . ".{$extension}";
            if (!rename($tempFile, $newPath)) {
                unlink($tempFile);
                throw new ValidationException('Failed to rename temporary file');
            }
            $tempFile = $newPath;
        }

        if (file_put_contents($tempFile, $content) === false) {
            unlink($tempFile);
            throw new ValidationException('Failed to write to temporary file');
        }

        return $tempFile;
    }

    /**
     * Clean up temporary files
     *
     * @param string ...$filePaths The file paths to clean up
     * @return void
     */
    public static function cleanupTempFiles(string ...$filePaths): void
    {
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                @unlink($filePath);
            }
        }
    }

    /**
     * Generate a unique filename
     *
     * @param string $originalName The original filename
     * @param string $prefix The prefix to add
     * @return string The unique filename
     */
    public static function generateUniqueFilename(string $originalName, string $prefix = ''): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);

        $timestamp = date('Ymd_His');
        $random = substr(md5(uniqid((string)mt_rand(), true)), 0, 8);

        $newName = $prefix . $basename . '_' . $timestamp . '_' . $random;

        if ($extension) {
            $newName .= '.' . $extension;
        }

        return $newName;
    }

    /**
     * Ensure directory exists
     *
     * @param string $directory The directory path
     * @throws ValidationException
     */
    public static function ensureDirectoryExists(string $directory): void
    {
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            throw new ValidationException("Cannot create directory: {$directory}");
        }
    }

    /**
     * Check if path is safe (no directory traversal)
     *
     * @param string $path The path to check
     * @return bool True if safe
     */
    public static function isPathSafe(string $path): bool
    {
        $realPath = realpath($path);

        // Check for directory traversal attempts
        if (strpos($path, '..') !== false) {
            return false;
        }

        // Ensure the path doesn't contain null bytes
        if (strpos($path, "\0") !== false) {
            return false;
        }

        return true;
    }
}
