# GrimReapper PDF Services PHP SDK

A comprehensive PHP SDK for Adobe PDF Services API that provides easy integration with all PDF manipulation, conversion, and processing features.

## Features

- **PDF Creation**: Generate PDFs from HTML, DOCX, images, and other formats
- **PDF Conversion**: Convert between PDF, DOCX, images, and other formats
- **PDF Merging**: Combine multiple PDFs into a single document
- **PDF Splitting**: Extract pages or split PDFs into multiple documents
- **OCR Processing**: Extract text from scanned documents
- **PDF Compression**: Reduce file sizes while maintaining quality
- **Security**: Password protection and permission management
- **Annotations**: Add comments, highlights, and markup to PDFs
- **Form Processing**: Extract and manipulate form data
- **Metadata Management**: Read and modify PDF metadata
- **Digital Signatures**: Add and validate electronic signatures
- **PDF Comparison**: Compare documents and generate diff reports
- **Batch Processing**: Process multiple operations efficiently

## Requirements

- PHP 8.1 or higher
- Composer for dependency management

## Installation

Install the package using Composer:

```bash
composer require grim-reapper/pdf-services-php
```

## Quick Start

### Basic Setup

```php
use GrimReapper\PdfServices\Client;
use GrimReapper\PdfServices\Config\PdfServicesConfig;

// Configure the client
$config = new PdfServicesConfig(
    apiKey: 'your-api-key',
    clientId: 'your-client-id',
    organizationId: 'your-organization-id'
);

// Create the client
$client = new Client($config);
```

### PDF Creation from HTML

```php
use GrimReapper\PdfServices\Models\Document;

// Get the PDF creation service
$creationService = $client->createPdf();

// Create PDF from HTML
$result = $creationService->fromHtml(
    html: '<h1>Hello World</h1><p>This is a PDF created with Adobe PDF Services.</p>',
    options: [
        'format' => 'A4',
        'margin' => ['top' => '1in', 'bottom' => '1in', 'left' => '1in', 'right' => '1in']
    ]
);

// Save the result
$result->saveTo('/path/to/output.pdf');
```

### PDF Conversion

```php
// Get the conversion service
$conversionService = $client->convert();

// Convert DOCX to PDF
$result = $conversionService->docxToPdf('/path/to/document.docx');
$result->saveTo('/path/to/output.pdf');

// Convert PDF to DOCX
$result = $conversionService->pdfToDocx('/path/to/document.pdf');
$result->saveTo('/path/to/output.docx');

// Convert image to PDF
$result = $conversionService->imageToPdf('/path/to/image.jpg');
$result->saveTo('/path/to/output.pdf');
```

### PDF Merging

```php
// Get the merge service
$mergeService = $client->merge();

// Merge multiple PDFs
$result = $mergeService->combine([
    '/path/to/document1.pdf',
    '/path/to/document2.pdf',
    '/path/to/document3.pdf'
]);

$result->saveTo('/path/to/merged.pdf');
```

### Digital Signatures

```php
// Get the signature service
$signatureService = $client->signature();

// Add a signature field
$result = $signatureService->addSignatureField('/path/to/document.pdf', [
    'name' => 'signature_field_1',
    'position' => ['x' => 100, 'y' => 100, 'width' => 200, 'height' => 50],
    'page' => 1
]);

// Add a digital signature
$result = $signatureService->addSignature('/path/to/document.pdf', [
    'certificate_path' => '/path/to/certificate.p12',
    'certificate_password' => 'password',
    'signature_field' => 'signature_field_1',
    'reason' => 'Document approval'
]);

$result->saveTo('/path/to/signed.pdf');
```

### PDF Comparison

```php
// Get the comparison service
$comparisonService = $client->compare();

// Compare two PDFs
$result = $comparisonService->comparePdfs(
    '/path/to/document_v1.pdf',
    '/path/to/document_v2.pdf'
);

if (!$result->areIdentical()) {
    echo "Found {$result->getDifferenceCount()} differences\n";

    // Generate a visual diff report
    $diffReport = $comparisonService->generateDiffReport(
        '/path/to/document_v1.pdf',
        '/path/to/document_v2.pdf',
        '/path/to/diff_report.pdf'
    );
}
```

## Configuration

### Environment Variables

You can configure the client using environment variables:

```bash
export GRIM_REAPPER_PDF_SERVICES_API_KEY="your-api-key"
export GRIM_REAPPER_PDF_SERVICES_CLIENT_ID="your-client-id"
export GRIM_REAPPER_PDF_SERVICES_ORGANIZATION_ID="your-organization-id"
export GRIM_REAPPER_PDF_SERVICES_ENVIRONMENT="production"
```

Then create the config from environment:

```php
$config = PdfServicesConfig::fromEnvironment();
```

### Custom HTTP Client

You can provide your own PSR-18 compatible HTTP client:

```php
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;

$guzzleClient = new GuzzleClient();
$requestFactory = new HttpFactory();
$streamFactory = new HttpFactory();

$config = new PdfServicesConfig('api-key', 'client-id', 'org-id');
$config->setHttpClient($guzzleClient);
$config->setRequestFactory($requestFactory);
$config->setStreamFactory($streamFactory);
```

## Error Handling

The SDK provides specific exception types for different error conditions:

```php
use GrimReapper\PdfServices\Exceptions\AuthenticationException;
use GrimReapper\PdfServices\Exceptions\ApiException;
use GrimReapper\PdfServices\Exceptions\ValidationException;

try {
    $result = $client->convert()->docxToPdf('/path/to/document.docx');
} catch (AuthenticationException $e) {
    // Handle authentication errors
    echo "Authentication failed: " . $e->getMessage();
} catch (ValidationException $e) {
    // Handle validation errors
    echo "Validation error: " . $e->getMessage();
} catch (ApiException $e) {
    // Handle API errors
    echo "API error: " . $e->getMessage();
    echo "Request ID: " . $e->getRequestId();
}
```

## Logging

Add logging to your application for debugging and monitoring:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('pdf-services');
$logger->pushHandler(new StreamHandler('logs/pdf-services.log', Logger::DEBUG));

// Set logger on services
$client->convert()->setLogger($logger);
$client->merge()->setLogger($logger);
```

## Advanced Usage

### Asynchronous Operations

Some operations support asynchronous processing for large files:

```php
// Start an asynchronous job
$job = $conversionService->docxToPdfAsync('/path/to/large-document.docx');

// Check job status
while (!$job->isCompleted()) {
    sleep(5); // Wait 5 seconds
    $job = $conversionService->getJobStatus($job->getJobId());
}

// Get the result when complete
if ($job->isCompleted()) {
    $result = $conversionService->getJobResult($job->getJobId());
    $result->saveTo('/path/to/output.pdf');
}
```

### Batch Processing

Process multiple operations efficiently in batches:

```php
// Get the batch processor service
$batchService = $client->batch();

// Define batch operations
$operationDefs = [
    [
        'type' => 'convert',
        'input' => 'input/document1.docx',
        'output' => 'output/document1.pdf'
    ],
    [
        'type' => 'convert',
        'input' => 'input/document2.docx',
        'output' => 'output/document2.pdf'
    ],
    [
        'type' => 'merge',
        'inputs' => ['output/document1.pdf', 'output/document2.pdf'],
        'output' => 'output/merged.pdf'
    ]
];

// Create and execute batch
$batch = $batchService->createBatchFromDefinitions($operationDefs);
$completedBatch = $batchService->executeBatch($batch);

// Get results
$results = $batchService->getBatchResults($completedBatch);
foreach ($results as $operationId => $document) {
    echo "Operation {$operationId} completed: {$document->getFilename()}\n";
}
```

### Advanced Batch Operations

Use BatchOperation objects for more control:

```php
use GrimReapper\PdfServices\Models\BatchOperation;

$operations = [
    BatchOperation::createConversion(
        'conv-1',
        'input/report.docx',
        'output/report.pdf',
        'docx',
        'pdf'
    ),
    BatchOperation::createMerge(
        'merge-1',
        ['output/file1.pdf', 'output/file2.pdf'],
        'output/combined.pdf'
    ),
    BatchOperation::createOcr(
        'ocr-1',
        'input/scanned.pdf',
        'output/searchable.pdf'
    )
];

$batch = $batchService->createBatch($operations);
```

## API Reference

For detailed API documentation, see the [API Reference](docs/api-reference.md).

## Examples

See the [examples](examples/) directory for complete working examples of all features.

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions:

- [Adobe PDF Services Documentation](https://developer.adobe.com/document-services/docs/overview/)
- [GitHub Issues](https://github.com/grim-reapper/pdf-services-php/issues)
- [Adobe Developer Forums](https://community.adobe.com/t5/document-services-apis/bd-p/DocumentServices-APIs)
