<?php

declare(strict_types=1);

namespace GrimReapper\Contracts;

use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Services\PdfCreationService;
use GrimReapper\PdfServices\Services\PdfConversionService;
use GrimReapper\PdfServices\Services\PdfMergeService;
use GrimReapper\PdfServices\Services\PdfSplitService;
use GrimReapper\PdfServices\Services\PageManipulationService;
use GrimReapper\PdfServices\Services\OcrService;
use GrimReapper\PdfServices\Services\CompressionService;
use GrimReapper\PdfServices\Services\SecurityService;
use GrimReapper\PdfServices\Services\AnnotationService;
use GrimReapper\PdfServices\Services\FormService;
use GrimReapper\PdfServices\Services\MetadataService;
use GrimReapper\PdfServices\Services\SignatureService;
use GrimReapper\PdfServices\Services\ComparisonService;
use GrimReapper\PdfServices\Services\BatchProcessorService;

/**
 * Interface for PDF Services client
 */
interface PdfServicesInterface
{
    /**
     * Get the PDF creation service
     *
     * @return PdfCreationService
     */
    public function createPdf(): PdfCreationService;

    /**
     * Get the PDF conversion service
     *
     * @return PdfConversionService
     */
    public function convert(): PdfConversionService;

    /**
     * Get the PDF merge service
     *
     * @return PdfMergeService
     */
    public function merge(): PdfMergeService;

    /**
     * Get the PDF split service
     *
     * @return PdfSplitService
     */
    public function split(): PdfSplitService;

    /**
     * Get the page manipulation service
     *
     * @return PageManipulationService
     */
    public function pageManipulation(): PageManipulationService;

    /**
     * Get the OCR service
     *
     * @return OcrService
     */
    public function ocr(): OcrService;

    /**
     * Get the compression service
     *
     * @return CompressionService
     */
    public function compress(): CompressionService;

    /**
     * Get the security service
     *
     * @return SecurityService
     */
    public function secure(): SecurityService;

    /**
     * Get the annotation service
     *
     * @return AnnotationService
     */
    public function annotate(): AnnotationService;

    /**
     * Get the form service
     *
     * @return FormService
     */
    public function extract(): FormService;

    /**
     * Get the metadata service
     *
     * @return MetadataService
     */
    public function metadata(): MetadataService;

    /**
     * Get the signature service
     *
     * @return SignatureService
     */
    public function signature(): SignatureService;

    /**
     * Get the comparison service
     *
     * @return ComparisonService
     */
    public function compare(): ComparisonService;

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
