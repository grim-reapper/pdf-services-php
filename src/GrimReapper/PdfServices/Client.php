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
    private array $services = [];

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
     * Get a service instance (lazy loaded)
     *
     * @param string $class The service class name
     * @return mixed The service instance
     */
    private function getService(string $class): mixed
    {
        if (!isset($this->services[$class])) {
            $this->services[$class] = new $class($this->config);
        }

        return $this->services[$class];
    }

    /**
     * Get the PDF creation service
     *
     * @return PdfCreationService
     */
    public function createPdf(): PdfCreationService
    {
        return $this->getService(PdfCreationService::class);
    }

    /**
     * Get the PDF conversion service
     *
     * @return PdfConversionService
     */
    public function convert(): PdfConversionService
    {
        return $this->getService(PdfConversionService::class);
    }

    /**
     * Get the PDF merge service
     *
     * @return PdfMergeService
     */
    public function merge(): PdfMergeService
    {
        return $this->getService(PdfMergeService::class);
    }

    /**
     * Get the PDF split service
     *
     * @return PdfSplitService
     */
    public function split(): PdfSplitService
    {
        return $this->getService(PdfSplitService::class);
    }

    /**
     * Get the OCR service
     *
     * @return OcrService
     */
    public function ocr(): OcrService
    {
        return $this->getService(OcrService::class);
    }

    /**
     * Get the compression service
     *
     * @return CompressionService
     */
    public function compress(): CompressionService
    {
        return $this->getService(CompressionService::class);
    }

    /**
     * Get the security service
     *
     * @return SecurityService
     */
    public function secure(): SecurityService
    {
        return $this->getService(SecurityService::class);
    }

    /**
     * Get the annotation service
     *
     * @return AnnotationService
     */
    public function annotate(): AnnotationService
    {
        return $this->getService(AnnotationService::class);
    }

    /**
     * Get the form service
     *
     * @return FormService
     */
    public function extract(): FormService
    {
        return $this->getService(FormService::class);
    }

    /**
     * Get the metadata service
     *
     * @return MetadataService
     */
    public function metadata(): MetadataService
    {
        return $this->getService(MetadataService::class);
    }

    /**
     * Get the signature service
     *
     * @return SignatureService
     */
    public function signature(): SignatureService
    {
        return $this->getService(SignatureService::class);
    }

    /**
     * Get the comparison service
     *
     * @return ComparisonService
     */
    public function compare(): ComparisonService
    {
        return $this->getService(ComparisonService::class);
    }

    /**
     * Get the batch processor service
     *
     * @return BatchProcessorService
     */
    public function batch(): BatchProcessorService
    {
        return $this->getService(BatchProcessorService::class);
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
