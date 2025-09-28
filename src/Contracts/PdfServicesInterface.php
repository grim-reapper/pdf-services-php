<?php

declare(strict_types=1);

namespace GrimReapper\Contracts;

use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Services\BatchProcessorService;

/**
 * Interface for PDF Services client
 */
interface PdfServicesInterface
{
    /**
     * Get the PDF creation service
     *
     * @return mixed
     */
    public function createPdf();

    /**
     * Get the PDF conversion service
     *
     * @return mixed
     */
    public function convert();

    /**
     * Get the PDF merge service
     *
     * @return mixed
     */
    public function merge();

    /**
     * Get the PDF split service
     *
     * @return mixed
     */
    public function split();

    /**
     * Get the OCR service
     *
     * @return mixed
     */
    public function ocr();

    /**
     * Get the compression service
     *
     * @return mixed
     */
    public function compress();

    /**
     * Get the security service
     *
     * @return mixed
     */
    public function secure();

    /**
     * Get the annotation service
     *
     * @return mixed
     */
    public function annotate();

    /**
     * Get the form service
     *
     * @return mixed
     */
    public function extract();

    /**
     * Get the metadata service
     *
     * @return mixed
     */
    public function metadata();

    /**
     * Get the signature service
     *
     * @return mixed
     */
    public function signature();

    /**
     * Get the comparison service
     *
     * @return mixed
     */
    public function compare();

    /**
     * Get the batch processor service
     *
     * @return BatchProcessorService
     */
    public function batch(): BatchProcessorService;

    /**
     * Get the current configuration
     *
     * @return PdfServicesConfig
     */
    public function getConfig(): PdfServicesConfig;
}
