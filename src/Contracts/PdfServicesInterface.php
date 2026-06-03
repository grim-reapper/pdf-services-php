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
use GrimReapper\PdfServices\Services\WatermarkService;
use GrimReapper\PdfServices\Services\AccessibilityService;
use GrimReapper\PdfServices\Services\MarkdownService;
use GrimReapper\PdfServices\Services\LinearizeService;
use GrimReapper\PdfServices\Services\DocumentGenerationService;
use GrimReapper\PdfServices\Services\ExtractService;

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
     * Get the extraction service
     *
     * @return ExtractService
     */
    public function extract(): ExtractService;

    /**
     * Get the watermark service
     *
     * @return WatermarkService
     */
    public function watermark(): WatermarkService;

    /**
     * Get the accessibility service
     *
     * @return AccessibilityService
     */
    public function accessibility(): AccessibilityService;

    /**
     * Get the markdown service
     *
     * @return MarkdownService
     */
    public function markdown(): MarkdownService;

    /**
     * Get the linearization service
     *
     * @return LinearizeService
     */
    public function linearize(): LinearizeService;

    /**
     * Get the document generation service
     *
     * @return DocumentGenerationService
     */
    public function documentGeneration(): DocumentGenerationService;

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
    public function forms(): FormService;

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
