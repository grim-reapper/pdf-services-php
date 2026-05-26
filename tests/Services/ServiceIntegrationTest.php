<?php

declare(strict_types=1);

namespace GrimReapper\Tests\Services;

use GrimReapper\PdfServices\Client;
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
use PHPUnit\Framework\TestCase;

class ServiceIntegrationTest extends TestCase
{
    private Client $client;

    protected function setUp(): void
    {
        $config = new PdfServicesConfig('client', 'secret', 'org');
        $this->client = new Client($config);
    }

    public function testServicesCanBeInstantiated(): void
    {
        $this->assertInstanceOf(PdfCreationService::class, $this->client->createPdf());
        $this->assertInstanceOf(PdfConversionService::class, $this->client->convert());
        $this->assertInstanceOf(PdfMergeService::class, $this->client->merge());
        $this->assertInstanceOf(PdfSplitService::class, $this->client->split());
        $this->assertInstanceOf(OcrService::class, $this->client->ocr());
        $this->assertInstanceOf(CompressionService::class, $this->client->compress());
        $this->assertInstanceOf(SecurityService::class, $this->client->secure());
        $this->assertInstanceOf(AnnotationService::class, $this->client->annotate());
        $this->assertInstanceOf(FormService::class, $this->client->extract());
        $this->assertInstanceOf(MetadataService::class, $this->client->metadata());
        $this->assertInstanceOf(SignatureService::class, $this->client->signature());
        $this->assertInstanceOf(ComparisonService::class, $this->client->compare());
        $this->assertInstanceOf(BatchProcessorService::class, $this->client->batch());
    }

    public function testServiceNames(): void
    {
        $this->assertEquals('htmltopdf', $this->client->createPdf()->getServiceName());
        $this->assertEquals('pdf-conversion', $this->client->convert()->getServiceName());
        $this->assertEquals('combinepdf', $this->client->merge()->getServiceName());
        $this->assertEquals('splitpdf', $this->client->split()->getServiceName());
        $this->assertEquals('ocr', $this->client->ocr()->getServiceName());
        $this->assertEquals('compresspdf', $this->client->compress()->getServiceName());
        $this->assertEquals('protectpdf', $this->client->secure()->getServiceName());
        $this->assertEquals('pdf-annotations', $this->client->annotate()->getServiceName());
        $this->assertEquals('form-data-extraction', $this->client->extract()->getServiceName());
        $this->assertEquals('pdfproperties', $this->client->metadata()->getServiceName());
        $this->assertEquals('signature', $this->client->signature()->getServiceName());
        $this->assertEquals('comparepdf', $this->client->compare()->getServiceName());
        $this->assertEquals('batch-processor', $this->client->batch()->getServiceName());
    }
}
