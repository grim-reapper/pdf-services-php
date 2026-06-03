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
  - [Linearize (Web Optimize)](#linearize-web-optimize)
  - [Form Processing](#form-processing)
  - [PDF Extraction](#pdf-extraction)
  - [PDF to Markdown](#pdf-to-markdown)
  - [PDF to Images](#pdf-to-images)
  - [Accessibility](#accessibility)
  - [Document Generation](#document-generation)
  - [Watermarking](#watermarking)
  - [Metadata](#metadata)
  - [Digital Signatures](#digital-signatures)
  - [Document Comparison](#document-comparison)
  - [Annotations](#annotations)
- [Advanced Usage](#advanced-usage)
  - [Batch Processing](#batch-processing)
  - [Webhooks (Notifiers)](#webhooks-notifiers)
- [Error Handling](#error-handling)

## Features

- **Full API v2 Support**: Uses the latest Adobe PDF Services asynchronous workflow.
- **All PDF Operations**: Create, convert, merge, split, compress, protect, extract, OCR, and more.
- **Lazy Loading**: Services are instantiated only when needed for better performance.
- **Robust Error Handling**: Standardized exceptions for API, authentication, and validation errors.
- **Regional Endpoints**: Support for US and EU Adobe regions.
- **Batch Processing**: Execute multiple operations sequentially with a single call.

## Requirements

- PHP 8.1 or higher
- Composer
- ext-zip (for ZIP output support)
- ext-curl (for HTTP requests)

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
    'ocrType' => 'searchable_image',
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

// Protect with password and permissions
$service->protect('input.pdf', [
    'password' => 'secret123',
    'permissions' => ['PRINT_LOW_RES']
])->saveTo('protected.pdf');

// Unprotect
$service->unprotect('protected.pdf', 'secret123')->saveTo('open.pdf');
```

### Linearize (Web Optimize)

Optimize a PDF for fast web viewing (linearized PDF).

```php
$client->linearize()->linearize('large.pdf')->saveTo('web-optimized.pdf');
```

### Form Processing

```php
$service = $client->forms();

// Fill form
$service->fillForm('template.pdf', ['first_name' => 'John'])->saveTo('filled.pdf');

// Export data (JSON)
$data = $service->exportFormData($doc, 'json');
```

### PDF Extraction

Extract text, tables, and images as structured data (ZIP output).

```php
// Extract text and tables
$zip = $client->extract()->extract('document.pdf', ['text', 'tables']);
$zip->saveTo('extracted_data.zip');

// Extract text only
$zip = $client->extract()->extract('document.pdf', ['text']);

// Extract with table images and figures
$zip = $client->extract()->extract('document.pdf', ['text'], [
    'renditionsToExtract' => ['tables', 'figures']
]);

// Extract tables as CSV
$zip = $client->extract()->extract('document.pdf', ['tables'], [
    'tableOutputFormat' => 'csv'
]);
```

### PDF to Markdown

Convert PDF to LLM-friendly Markdown (returns ZIP with `markdown.json`).

```php
$service = $client->markdown();

// Basic conversion (returns ZIP with markdown.json)
$service->toMarkdown('document.pdf')->saveTo('markdown_output.zip');

// Include base64-encoded figures
$service->toMarkdown('document.pdf', ['getFigures' => true])->saveTo('markdown_with_images.zip');
```

### PDF to Images

Convert PDF pages to image files (JPEG, PNG, TIFF).

```php
$service = $client->images();

// Convert to JPEG (default, returns ZIP)
$service->toJpeg('document.pdf')->saveTo('images-jpeg.zip');

// Convert to PNG
$service->toPng('document.pdf')->saveTo('images-png.zip');

// Convert to TIFF
$service->toTiff('document.pdf')->saveTo('images-tiff.zip');

// Or use the general method with custom options
$service->toImages('document.pdf', 'jpeg', 'zipOfPageImages')->saveTo('output.zip');
```

### Accessibility

```php
$service = $client->accessibility();

// Auto-tag for accessibility (returns [taggedPdf, report])
[$taggedDoc, $report] = $service->autoTag('input.pdf', ['generateReport' => true]);
$taggedDoc->saveTo('tagged.pdf');

// Check accessibility (returns HTML report)
$reportHtml = $service->check('input.pdf');
$reportHtml->saveTo('accessibility_report.html');
```

### Document Generation

Merge Word templates with dynamic data to create PDF or DOCX.

```php
$client->documentGeneration()->generate(
    templatePath: 'invoice_template.docx',
    jsonData: ['invoice_id' => '123', 'amount' => 50.00]
)->saveTo('invoice.pdf');
```

### Watermarking

Add a watermark to PDF pages using a source watermark PDF.

```php
$client->watermark()->addWatermark(
    'document.pdf',
    'watermark_source.pdf',
    ['appearance' => ['opacity' => 50]]
)->saveTo('watermarked.pdf');
```

### Metadata

Read and update PDF metadata.

```php
$service = $client->metadata();

// Get metadata
$metadata = $service->getMetadata('document.pdf');
echo $metadata['title'] ?? 'Unknown';

// Update metadata
$service->updateMetadata('document.pdf', [
    'title' => 'New Title',
    'author' => 'Jane Doe'
])->saveTo('updated.pdf');
```

### Digital Signatures

Apply digital signatures to PDF documents.

```php
$client->signature()->sign('document.pdf', [
    'signatureFieldName' => 'Signature1',
    'pageNumber' => 1,
    'location' => ['top' => 400, 'left' => 50]
])->saveTo('signed.pdf');
```

### Document Comparison

Compare two PDF documents and highlight differences.

```php
$diff = $client->compare()->compare('original.pdf', 'modified.pdf');
$diff->saveTo('comparison_result.pdf');
```

### Annotations

Add and manage annotations on PDF documents.

```php
$client->annotate()->addAnnotation('document.pdf', [
    'type' => 'text',
    'content' => 'This is an important note',
    'pageNumber' => 1,
    'position' => ['x' => 100, 'y' => 200]
])->saveTo('annotated.pdf');
```

---

## Advanced Usage

### Batch Processing

Execute multiple operations sequentially with a single call. Operations are saved to disk automatically when an `output` path is specified.

```php
$batchService = $client->batch();

// Define operations
$operationDefs = [
    ['type' => 'convert', 'input' => 'doc1.docx', 'output' => 'doc1.pdf'],
    ['type' => 'convert', 'input' => 'doc2.docx', 'output' => 'doc2.pdf'],
    ['type' => 'compress', 'input' => 'large.pdf', 'output' => 'small.pdf', 'options' => ['compressionLevel' => 'MEDIUM']],
];

// Create and execute batch
$batch = $batchService->createBatchFromDefinitions($operationDefs);
$results = $batchService->executeBatch($batch);

// Or use a single method call
$batch = $batchService->createAndExecuteBatch($operationDefs);

// Check results
foreach ($results->getResults() as $operationId => $result) {
    if ($result['status'] === 'completed') {
        echo "{$operationId}: succeeded\n";
    } else {
        echo "{$operationId}: failed - {$result['error']}\n";
    }
}

// Get Document objects for custom saving
$documents = $batchService->getBatchResults($results);
foreach ($documents as $operationId => $doc) {
    $doc->saveTo("custom_{$operationId}.pdf");
}
```

**Supported batch operation types:**

| Type | Description | Required Fields |
|------|-------------|-----------------|
| `convert` | DOCX/XLSX/PPTX/image to PDF | `input`, `output` |
| `compress` | Compress PDF | `input`, `output` |
| `merge` | Combine multiple PDFs | `input[]` (array), `output` |
| `ocr` | OCR a scanned PDF | `input`, `output` |
| `export` | PDF to DOCX/XLSX/PPTX | `input`, `output` |
| `linearize` | Web-optimize a PDF | `input`, `output` |
| `protect` | Add password protection | `input`, `output` |
| `split` | Split PDF into ranges | `input`, `output` |

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