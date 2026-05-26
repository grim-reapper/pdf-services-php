# GrimReapper PDF Services PHP SDK

A comprehensive PHP SDK for Adobe PDF Services API (v2) that provides easy integration with all PDF manipulation, conversion, and processing features.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Available Services](#available-services)
  - [PDF Creation](#pdf-creation)
  - [PDF Conversion](#pdf-conversion)
  - [PDF Merging & Splitting](#pdf-merging--splitting)
  - [Page Manipulation](#page-manipulation)
  - [OCR Processing](#ocr-processing)
  - [PDF Compression](#pdf-compression)
  - [Security](#security)
  - [Form Processing](#form-processing)
  - [PDF Extraction](#pdf-extraction)
  - [Accessibility](#accessibility)
  - [Document Generation](#document-generation)
  - [Watermarking](#watermarking)
- [Advanced Usage](#advanced-usage)
  - [Asynchronous Operations](#asynchronous-operations)
  - [Batch Processing](#batch-processing)
  - [Webhooks (Notifiers)](#webhooks-notifiers)
- [Error Handling](#error-handling)

## Features

- **Full API v2 Support**: Uses the latest Adobe PDF Services asynchronous workflow.
- **Lazy Loading**: Services are instantiated only when needed for better performance.
- **Robust Error Handling**: Standardized exceptions for API, authentication, and validation errors.
- **Regional Endpoints**: Support for US and EU Adobe regions.

## Requirements

- PHP 8.1 or higher
- Composer

## Installation

```bash
composer require grim-reapper/pdf-services-php
```

## Quick Start

### Basic Setup

```php
use GrimReapper\PdfServices\Client;
use GrimReapper\PdfServices\Config\PdfServicesConfig;

$config = new PdfServicesConfig(
    clientId: 'your-client-id',
    clientSecret: 'your-client-secret',
    organizationId: 'your-organization-id',
    region: 'us' // 'us' (default) or 'eu'
);

$client = new Client($config);
```

---

## Available Services

### PDF Creation

Convert HTML or URLs to PDF.

```php
$service = $client->createPdf();

// From HTML string
$pdf = $service->fromHtml('<h1>Hello World</h1>', [
    'format' => 'A4',
    'includeHeaderFooter' => true
]);
$pdf->saveTo('output.pdf');

// From URL
$pdf = $service->fromUrl('https://example.com');
$pdf->saveTo('website.pdf');
```

### PDF Conversion

Convert between various formats.

```php
$service = $client->convert();

// DOCX to PDF
$pdf = $service->docxToPdf('document.docx');

// PDF to DOCX
$docx = $service->pdfToDocx('input.pdf');

// Image to PDF
$pdf = $service->imageToPdf('photo.jpg');
```

### PDF Merging & Splitting

```php
// Merging
$client->merge()->combine(['file1.pdf', 'file2.pdf'])->saveTo('merged.pdf');

// Splitting
$docs = $client->split()->split('input.pdf', [
    'pageRanges' => [['start' => 1, 'end' => 2]]
]);
foreach ($docs as $i => $doc) {
    $doc->saveTo("part_$i.pdf");
}
```

### Page Manipulation

Delete, rotate, reorder, insert, or replace pages.

```php
$service = $client->pageManipulation();
$doc = $client->convert()->docxToPdf('input.docx'); // Get a Document object

// Delete pages
$service->deletePages($doc, [['start' => 1, 'end' => 1]])->saveTo('deleted.pdf');

// Rotate pages (90, 180, 270)
$service->rotatePages($doc, 90)->saveTo('rotated.pdf');

// Insert pages from another doc
$otherDoc = $client->createPdf()->fromHtml('<h1>New Page</h1>');
$service->insertPages($doc, $otherDoc, atPage: 1)->saveTo('inserted.pdf');
```

### OCR Processing

```php
$client->ocr()->ocr('scanned.pdf', [
    'ocrType' => 'searchable_pdf',
    'ocrLang' => 'en-US'
])->saveTo('searchable.pdf');
```

### PDF Compression

```php
$client->compress()->compress('large.pdf', ['compressionLevel' => 'MEDIUM'])->saveTo('small.pdf');
```

### Security

```php
$service = $client->secure();

// Protect
$service->protect('input.pdf', [
    'password' => 'secret123',
    'permissions' => ['PRINT_LOW_RES']
])->saveTo('protected.pdf');

// Unprotect
$service->unprotect('protected.pdf', 'secret123')->saveTo('open.pdf');
```

### Form Processing

```php
$service = $client->forms();

// Fill form
$service->fillForm('template.pdf', ['first_name' => 'John'])->saveTo('filled.pdf');

// Export data (JSON/XFDF)
$data = $service->exportFormData($doc, 'json');
```

### PDF Extraction

Extract text, tables, and images as structured data.

```php
$zip = $client->extract()->extract('input.pdf', ['text', 'tables']);
$zip->saveTo('extracted_data.zip');
```

### Accessibility

```php
$service = $client->accessibility();

// Auto-tag for accessibility
[$taggedDoc, $report] = $service->autoTag('input.pdf', ['generateReport' => true]);

// Check accessibility
$reportHtml = $service->check('input.pdf');
$reportHtml->saveTo('accessibility_report.html');
```

### Document Generation

Merge Word templates with dynamic data.

```php
$client->documentGeneration()->generate(
    templatePath: 'invoice_template.docx',
    jsonData: ['invoice_id' => '123', 'amount' => 50.00]
)->saveTo('invoice.pdf');
```

### Watermarking

```php
$client->watermark()->addWatermark(
    'document.pdf',
    'watermark_source.pdf',
    ['appearance' => ['opacity' => 50]]
)->saveTo('watermarked.pdf');
```

---

## Advanced Usage

### Asynchronous Operations

For large files, use async methods to avoid timeouts.

```php
$service = $client->convert();
$job = $service->docxToPdfAsync('large_file.docx');

while (!$job->isCompleted()) {
    sleep(5);
    $job = $service->getJobStatus($job->getJobId());

    if ($job->isFailed()) {
        die("Job failed: " . $job->getError());
    }
}

$result = $service->getJobResult($job->getJobId());
$result->saveTo('finished.pdf');
```

### Batch Processing

```php
$batchService = $client->batch();
$operationDefs = [
    ['type' => 'convert', 'input' => 'doc1.docx', 'output' => 'doc1.pdf'],
    ['type' => 'convert', 'input' => 'doc2.docx', 'output' => 'doc2.pdf']
];

$batch = $batchService->createBatchFromDefinitions($operationDefs);
$results = $batchService->executeBatch($batch);
```

### Webhooks (Notifiers)

You can configure webhooks to be notified when a job is done.

```php
$config->setNotifiers([
    [
        'type' => 'CALLBACK',
        'url' => 'https://your-app.com/webhook-handler'
    ]
]);
```

## Error Handling

```php
use GrimReapper\PdfServices\Exceptions\ApiException;

try {
    $client->convert()->docxToPdf('missing.docx');
} catch (ApiException $e) {
    echo "Error: " . $e->getMessage();
    echo "Status Code: " . $e->getCode();
    echo "Request ID: " . $e->getRequestId();
}
```

## License

MIT
