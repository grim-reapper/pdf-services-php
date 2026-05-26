<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices;

use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Http\HttpClient;
use GrimReapper\PdfServices\Services\AuthService;
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
    private HttpClient $httpClient;
    private AuthService $authService;
    private array $services = [];

    /**
     * Create a new PDF Services client
     *
     * @param PdfServicesConfig $config Configuration for the PDF Services API
     */
    public function __construct(PdfServicesConfig $config)
    {
        $this->config = $config;
        $this->httpClient = new HttpClient($config);
        $this->authService = new AuthService($config);
        $this->httpClient->setAuthService($this->authService);
    }

    /**
     * Get the page manipulation service
     *
     * @return PageManipulationService
     */
    public function pageManipulation(): PageManipulationService
    {
        return $this->getService(PageManipulationService::class);
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
            $this->services[$class] = new $class($this->config, $this->httpClient);
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
    public function forms(): FormService
    {
        return $this->getService(FormService::class);
    }

    /**
     * Get the extraction service
     *
     * @return ExtractService
     */
    public function extract(): ExtractService
    {
        return $this->getService(ExtractService::class);
    }

    /**
     * Get the watermark service
     *
     * @return WatermarkService
     */
    public function watermark(): WatermarkService
    {
        return $this->getService(WatermarkService::class);
    }

    /**
     * Get the accessibility service
     *
     * @return AccessibilityService
     */
    public function accessibility(): AccessibilityService
    {
        return $this->getService(AccessibilityService::class);
    }

    /**
     * Get the markdown service
     *
     * @return MarkdownService
     */
    public function markdown(): MarkdownService
    {
        return $this->getService(MarkdownService::class);
    }

    /**
     * Get the linearization service
     *
     * @return LinearizeService
     */
    public function linearize(): LinearizeService
    {
        return $this->getService(LinearizeService::class);
    }

    /**
     * Get the document generation service
     *
     * @return DocumentGenerationService
     */
    public function documentGeneration(): DocumentGenerationService
    {
        return $this->getService(DocumentGenerationService::class);
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
