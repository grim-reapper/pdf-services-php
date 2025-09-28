<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices;

use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Services\PdfCreationService;
use GrimReapper\PdfServices\Services\PdfConversionService;
use GrimReapper\PdfServices\Services\PdfMergeService;
use GrimReapper\PdfServices\Services\PdfSplitService;
use GrimReapper\PdfServices\Services\OcrService;
use GrimReapper\PdfServices\Services\CompressionService;
use GrimReapper\PdfServices\Services\SecurityService;
use GrimReapper\PdfServices\Services\AnnotationService;
use GrimReapper\PdfServices\Services\FormService;
use GrimReapper\PdfServices\Services\MetadataService;
use GrimReapper\PdfServices\Services\SignatureService;
use GrimReapper\PdfServices\Services\ComparisonService;
use GrimReapper\PdfServices\Services\BatchProcessorService;
use GrimReapper\Contracts\PdfServicesInterface;

/**
 * Main client for Adobe PDF Services API
 *
 * This class provides access to all PDF services and manages authentication
 * and configuration for the Adobe PDF Services API.
 */
class Client implements PdfServicesInterface
{
    private PdfServicesConfig $config;

    /**
     * Create a new PDF Services client
     *
     * @param PdfServicesConfig $config Configuration for the PDF Services API
     */
    public function __construct(PdfServicesConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get the PDF creation service
     *
     * @return PdfCreationService
     */
    public function createPdf(): PdfCreationService
    {
        return new PdfCreationService($this->config);
    }

    /**
     * Get the PDF conversion service
     *
     * @return PdfConversionService
     */
    public function convert(): PdfConversionService
    {
        return new PdfConversionService($this->config);
    }

    /**
     * Get the PDF merge service
     *
     * @return PdfMergeService
     */
    public function merge(): PdfMergeService
    {
        return new PdfMergeService($this->config);
    }

    /**
     * Get the PDF split service
     *
     * @return PdfSplitService
     */
    public function split(): PdfSplitService
    {
        return new PdfSplitService($this->config);
    }

    /**
     * Get the OCR service
     *
     * @return OcrService
     */
    public function ocr(): OcrService
    {
        return new OcrService($this->config);
    }

    /**
     * Get the compression service
     *
     * @return CompressionService
     */
    public function compress(): CompressionService
    {
        return new CompressionService($this->config);
    }

    /**
     * Get the security service
     *
     * @return SecurityService
     */
    public function secure(): SecurityService
    {
        return new SecurityService($this->config);
    }

    /**
     * Get the annotation service
     *
     * @return AnnotationService
     */
    public function annotate(): AnnotationService
    {
        return new AnnotationService($this->config);
    }

    /**
     * Get the form service
     *
     * @return FormService
     */
    public function extract(): FormService
    {
        return new FormService($this->config);
    }

    /**
     * Get the metadata service
     *
     * @return MetadataService
     */
    public function metadata(): MetadataService
    {
        return new MetadataService($this->config);
    }

    /**
     * Get the signature service
     *
     * @return SignatureService
     */
    public function signature(): SignatureService
    {
        return new SignatureService($this->config);
    }

    /**
     * Get the comparison service
     *
     * @return ComparisonService
     */
    public function compare(): ComparisonService
    {
        return new ComparisonService($this->config);
    }

    /**
     * Get the batch processor service
     *
     * @return BatchProcessorService
     */
    public function batch(): BatchProcessorService
    {
        return new BatchProcessorService($this->config);
    }

    /**
     * Get the current configuration
     *
     * @return PdfServicesConfig
     */
    public function getConfig(): PdfServicesConfig
    {
        return $this->config;
    }
}
