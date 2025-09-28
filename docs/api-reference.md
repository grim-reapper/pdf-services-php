# API Reference

This comprehensive API reference covers all classes, interfaces, and methods available in the Adobe PDF Services PHP SDK. Each section includes method signatures, parameters, return types, and usage examples.

## 📚 Table of Contents

- [Core Classes](#core-classes)
  - [Client](#client)
  - [Configuration](#configuration)
- [Models](#models)
  - [Document](#document)
  - [Job](#job)
  - [Batch & BatchOperation](#batch--batchoperation)
  - [Signature](#signature)
  - [Comparison & ComparisonResult](#comparison--comparisonresult)
- [Services](#services)
  - [Service Interface](#service-interface)
  - [Available Services](#available-services)
- [Exceptions](#exceptions)
- [HTTP Utilities](#http-utilities)
- [Utilities](#utilities)

## 🏗️ Core Classes

### Client

The main entry point for accessing Adobe PDF Services API functionality.

**Namespace**: `GrimReapper\PdfServices\Client`

#### Constructor

```php
public function __construct(PdfServicesConfig $config)
```

**Parameters**:
- `config` (PdfServicesConfig): SDK configuration with credentials

**Throws**:
- ValidationException: If configuration is invalid

#### Service Methods

```php
public function createPdf(): PdfCreationService
public function convert(): PdfConversionService
public function merge(): PdfMergeService
public function signature(): PdfSignatureService
public function compare(): PdfComparisonService
public function batch(): BatchProcessorService
```

**Returns**:
- Service instances for different PDF operations

#### Example Usage

```php
use GrimReapper\PdfServices\Client;
use GrimReapper\PdfServices\Config\PdfServicesConfig;

$config = new PdfServicesConfig('api-key', 'client-id', 'org-id');
$client = new Client($config);

// Get services
$conversionService = $client->convert();
$mergeService = $client->merge();
```

---

## ⚙️ Configuration

### PdfServicesConfig

Configuration class for SDK settings and credentials.

**Namespace**: `GrimReapper\PdfServices\Config\PdfServicesConfig`

#### Constructor

```php
public function __construct(
    string $apiKey,
    string $clientId,
    string $organizationId,
    ?string $environment = null
)
```

**Parameters**:
- `apiKey` (string): Adobe PDF Services API key
- `clientId` (string): OAuth client ID
- `organizationId` (string): Organization ID
- `environment` (string, optional): Environment name ('production', 'stage')

#### Static Factory Methods

```php
public static function fromEnvironment(): self
public static function fromArray(array $config): self
```

#### HTTP Client Configuration

```php
public function setHttpClient(Psr\Http\Client\ClientInterface $client): self
public function setRequestFactory(Psr\Http\Message\RequestFactoryInterface $factory): self
public function setStreamFactory(Psr\Http\Message\StreamFactoryInterface $factory): self
```

**Parameters**:
- `client` (ClientInterface): PSR-18 compatible HTTP client
- `factory` (RequestFactoryInterface): PSR-17 request factory
- `factory` (StreamFactoryInterface): PSR-17 stream factory

**Returns**:
- Self for method chaining

#### Example Usage

```php
use GrimReapper\PdfServices\Config\PdfServicesConfig;

// Direct configuration
$config = new PdfServicesConfig(
    apiKey: 'your-api-key',
    clientId: 'your-client-id',
    organizationId: 'your-org-id'
);

// Environment configuration
$config = PdfServicesConfig::fromEnvironment();

// Custom HTTP client
$config->setHttpClient($customClient);
```

---

## 📄 Models

### Document

Represents a PDF document with its content and metadata.

**Namespace**: `GrimReapper\PdfServices\Models\Document`

#### Constructor

```php
public function __construct(
    string $content,
    string $mimeType = 'application/pdf',
    ?string $filename = null,
    ?int $size = null
)
```

**Parameters**:
- `content` (string): Document content (base64 encoded for binary files)
- `mimeType` (string): MIME type of the document
- `filename` (string, optional): Original filename
- `size` (int, optional): File size in bytes

#### Getters

```php
public function getContent(): string
public function getMimeType(): string
public function getFilename(): ?string
public function getSize(): ?int
```

#### Instance Methods

```php
public function isPdf(): bool
```

**Returns**:
- `true` if document is PDF format

```php
public function saveTo(string $path): bool
```

**Parameters**:
- `path` (string): File system path to save document

**Returns**:
- `true` on success

**Throws**:
- ValidationException: If path is invalid or save fails

#### Static Factory Methods

```php
public static function fromString(
    string $content,
    string $mimeType = 'text/plain',
    ?string $filename = null
): self
```

```php
public static function fromFile(string $filePath): self
```

**Parameters**:
- `filePath` (string): Path to file to load

**Throws**:
- ValidationException: If file doesn't exist or is not readable

#### Example Usage

```php
use GrimReapper\PdfServices\Models\Document;

// Create from string content
$document = Document::fromString(
    '<h1>Hello World</h1>',
    'text/html',
    'index.html'
);

// Load from file
$document = Document::fromFile('/path/to/document.pdf');

// Save to new location
$document->saveTo('/path/to/output.pdf');
```

---

### Job

Represents an asynchronous job operation.

**Namespace**: `GrimReapper\PdfServices\Models\Job`

#### Constructor

```php
public function __construct(
    string $jobId,
    string $status = 'in_progress',
    ?DateTimeInterface $createdAt = null,
    ?DateTimeInterface $updatedAt = null,
    ?array $result = null,
    ?string $error = null
)
```

#### Getters

```php
public function getJobId(): string
public function getStatus(): string
public function isCompleted(): bool
public function isInProgress(): bool
public function isFailed(): bool
public function getCreatedAt(): ?DateTimeInterface
public function getUpdatedAt(): ?DateTimeInterface
public function getResult(): ?array
public function getError(): ?string
```

#### Instance Methods

```php
public function updateStatus(
    string $status,
    ?array $result = null,
    ?string $error = null
): self
```

#### Static Factory Methods

```php
public static function fromApiResponse(array $response): self
```

#### Example Usage

```php
use GrimReapper\PdfServices\Models\Job;

// Create new job
$job = new Job('job-123');

// Check status
if ($job->isCompleted()) {
    $result = $job->getResult();
} elseif ($job->isFailed()) {
    $error = $job->getError();
}

// Update status
$job->updateStatus('completed', ['data' => 'result']);
```

---

### Batch & BatchOperation

**Namespace**: `GrimReapper\PdfServices\Models\Batch`, `GrimReapper\PdfServices\Models\BatchOperation`

#### Batch Constructor

```php
public function __construct(
    string $batchId,
    array $operations,
    string $status = 'pending'
)
```

#### Batch Methods

```php
public function getBatchId(): string
public function getOperations(): array
public function getStatus(): string
public function isPending(): bool
public function isProcessing(): bool
public function isCompleted(): bool
public function isFailed(): bool
public function getCreatedAt(): ?DateTimeInterface
public function getCompletedAt(): ?DateTimeInterface
public function getResults(): array
public function getOperationCount(): int
public function getCompletedCount(): int
public function getFailedCount(): int
public function updateStatus(string $status, ?array $results = null, ?string $error = null): self
public static function fromApiResponse(array $response): self
```

#### BatchOperation Constructor

```php
public function __construct(
    string $operationId,
    string $type,
    array $input,
    array $output = [],
    array $options = []
)
```

#### BatchOperation Methods

```php
public function getOperationId(): string
public function getType(): string
public function getInput(): array
public function getOutput(): array
public function getOptions(): array
public function getStatus(): string
public function isPending(): bool
public function isCompleted(): bool
public function isFailed(): bool
public function updateStatus(string $status, ?string $error = null): self
public function toArray(): array
public static function fromArray(array $data): self
```

#### BatchOperation Static Factory Methods

```php
public static function createConversion(
    string $operationId,
    string $inputFile,
    string $outputFile,
    string $fromFormat,
    string $toFormat,
    array $options = []
): self

public static function createMerge(
    string $operationId,
    array $inputFiles,
    string $outputFile,
    array $options = []
): self

public static function createOcr(
    string $operationId,
    string $inputFile,
    string $outputFile,
    array $options = []
): self
```

#### Example Usage

```php
use GrimReapper\PdfServices\Models\Batch;
use GrimReapper\PdfServices\Models\BatchOperation;

// Create operations
$operations = [
    BatchOperation::createConversion(
        'conv1',
        'input.docx',
        'output.pdf',
        'docx',
        'pdf'
    ),
    BatchOperation::createMerge(
        'merge1',
        ['file1.pdf', 'file2.pdf'],
        'combined.pdf'
    )
];

// Create batch
$batch = new Batch('batch-123', $operations);

// Check progress
$completedCount = $batch->getCompletedCount();
$failedCount = $batch->getFailedCount();
```

---

### Signature

Represents digital signature information.

**Namespace**: `GrimReapper\PdfServices\Models\Signature`

#### Constructor

```php
public function __construct(
    string $signerName,
    ?DateTimeInterface $signingTime = null,
    bool $isValid = false,
    ?array $certificateInfo = null,
    ?string $reason = null,
    ?string $location = null,
    ?string $contactInfo = null
)
```

#### Getters

```php
public function getSignerName(): string
public function getSigningTime(): ?DateTimeInterface
public function isValid(): bool
public function getCertificateInfo(): ?array
public function getReason(): ?string
public function getLocation(): ?string
public function getContactInfo(): ?string
```

#### Static Factory Methods

```php
public static function fromApiResponse(array $response): self
```

#### Example Usage

```php
use GrimReapper\PdfServices\Models\Signature;

// Create signature (usually from API response)
$signature = Signature::fromApiResponse([
    'name' => 'John Doe',
    'date' => '2023-01-01T10:00:00Z',
    'valid' => true,
    'reason' => 'Document approval'
]);

echo "Signed by: " . $signature->getSignerName();
echo "Valid: " . ($signature->isValid() ? 'Yes' : 'No');
```

---

### Comparison & ComparisonResult

**Namespace**: `GrimReapper\PdfServices\Models\Comparison`, `GrimReapper\PdfServices\Models\ComparisonResult`

#### Comparison Constructor

```php
public function __construct(
    string $type,
    int $pageNumber,
    string $description,
    array $position = [],
    ?string $oldValue = null,
    ?string $newValue = null
)
```

#### Comparison Methods

```php
public function getType(): string
public function getPageNumber(): int
public function getDescription(): string
public function getPosition(): array
public function getOldValue(): ?string
public function getNewValue(): ?string
public function isTextDifference(): bool
public function isVisualDifference(): bool
public function isStructuralDifference(): bool
public static function fromApiResponse(array $response): self
```

#### ComparisonResult Constructor

```php
public function __construct(
    bool $identical = true,
    int $differenceCount = 0,
    array $differences = [],
    array $visualDifferences = [],
    array $textDifferences = []
)
```

#### ComparisonResult Methods

```php
public function areIdentical(): bool
public function getDifferenceCount(): int
public function getDifferences(): array
public function getVisualDifferences(): array
public function getTextDifferences(): array
public function addDifference(Comparison $difference): self
public static function fromApiResponse(array $response): self
```

#### Example Usage

```php
use GrimReapper\PdfServices\Models\ComparisonResult;

// Check if documents are identical
if ($result->areIdentical()) {
    echo "Documents are identical";
} else {
    echo "Found " . $result->getDifferenceCount() . " differences";

    // Get text differences
    foreach ($result->getTextDifferences() as $diff) {
        echo "Page {$diff->getPageNumber()}: {$diff->getDescription()}\n";
        echo "Old: {$diff->getOldValue()}\n";
        echo "New: {$diff->getNewValue()}\n";
    }
}
```

---

## 🔧 Services

### Service Interface

All services implement this common interface.

**Namespace**: `GrimReapper\Contracts\ServiceInterface`

#### Methods

```php
public function setLogger(Psr\Log\LoggerInterface $logger): self
public function getLogger(): ?Psr\Log\LoggerInterface
```

### Available Services

#### PdfCreationService

**Namespace**: `GrimReapper\PdfServices\Services\PdfCreationService`

Methods for generating PDFs from various sources:

```php
public function fromHtml(string $html, array $options = []): Document
public function fromUrl(string $url, array $options = []): Document
public function fromDocx(string $docxPath, array $options = []): Document
```

#### PdfConversionService

**Namespace**: `GrimReapper\PdfServices\Services\PdfConversionService`

Methods for converting between formats:

```php
public function docxToPdf(string $docxPath): Document
public function pdfToDocx(string $pdfPath): Document
public function imageToPdf(mixed $imageInput): Document
public function htmlToPdf(string $html, array $options = []): Document
```

#### PdfMergeService

**Namespace**: `GrimReapper\PdfServices\Services\PdfMergeService`

Methods for combining PDFs:

```php
public function combine(array $pdfPaths, array $options = []): Document
public function insertPages(string $basePdf, string $insertPdf, int $position): Document
public function extractPages(string $pdfPath, array $pageRanges): Document
```

#### PdfSignatureService

**Namespace**: `GrimReapper\PdfServices\Services\PdfSignatureService`

Methods for digital signatures:

```php
public function addSignatureField(string $pdfPath, array $fieldOptions): Document
public function addSignature(string $pdfPath, array $signatureOptions): Document
public function validateSignatures(string $pdfPath): array
```

#### PdfComparisonService

**Namespace**: `GrimReapper\PdfServices\Services\PdfComparisonService`

Methods for document comparison:

```php
public function comparePdfs(string $pdf1Path, string $pdf2Path): ComparisonResult
public function generateDiffReport(string $pdf1Path, string $pdf2Path, string $outputPath): Document
```

#### BatchProcessorService

**Namespace**: `GrimReapper\PdfServices\Services\BatchProcessorService`

Methods for batch processing:

```php
public function createBatch(array $operations): Batch
public function executeBatch(Batch $batch): Batch
public function getBatchStatus(string $batchId): Batch
public function getBatchResults(Batch $batch): array
```

---

## ⚠️ Exceptions

### Exception Hierarchy

All exceptions inherit from the base SDK exception:

```
GrimReapper\PdfServices\Exceptions\PdfServicesException (extends Exception)
├── GrimReapper\PdfServices\Exceptions\AuthenticationException
├── GrimReapper\PdfServices\Exceptions\ApiException
├── GrimReapper\PdfServices\Exceptions\ValidationException
├── GrimReapper\PdfServices\Exceptions\SignatureException
└── GrimReapper\PdfServices\Exceptions\ComparisonException
```

### Base Exception Class

**Namespace**: `GrimReapper\PdfServices\Exceptions\PdfServicesException`

#### Properties

```php
protected ?string $requestId;
protected array $responseHeaders;
protected int $responseStatusCode;
protected string $responseBody;
protected ?Throwable $previous;
```

#### Methods

```php
public function getRequestId(): ?string
public function getResponseHeaders(): array
public function getResponseStatusCode(): int
public function getResponseBody(): string
public function getPrevious(): ?Throwable
```

### Specific Exception Types

#### AuthenticationException

Thrown when API credentials are invalid or missing.

```php
class AuthenticationException extends PdfServicesException
```

#### ApiException

Thrown for API-level errors (rate limits, service unavailable, etc.).

```php
class ApiException extends PdfServicesException
{
    public function isRateLimitError(): bool
    public function isServiceUnavailable(): bool
    public function getRetryAfter(): ?int
}
```

#### ValidationException

Thrown for input validation errors.

```php
class ValidationException extends PdfServicesException
{
    public function getValidationErrors(): array
}
```

#### SignatureException

Thrown for digital signature operations errors.

```php
class SignatureException extends PdfServicesException
{
    public function getSignatureField(): ?string
    public function isCertificateError(): bool
}
```

#### ComparisonException

Thrown for document comparison errors.

```php
class ComparisonException extends PdfServicesException
{
    public function isFileFormatError(): bool
    public function isCorruptFileError(): bool
}
```

### Example Usage

```php
use GrimReapper\PdfServices\Exceptions\ApiException;
use GrimReapper\PdfServices\Exceptions\AuthenticationException;
use GrimReapper\PdfServices\Exceptions\ValidationException;

try {
    $result = $client->convert()->docxToPdf('/path/to/document.docx');
} catch (AuthenticationException $e) {
    echo "Authentication failed. Check your API credentials.\n";
    echo "Request ID: " . $e->getRequestId() . "\n";
} catch (ValidationException $e) {
    echo "Input validation error: " . $e->getMessage() . "\n";
    echo "Validation errors: " . implode(', ', $e->getValidationErrors()) . "\n";
} catch (ApiException $e) {
    echo "API error: " . $e->getMessage() . "\n";
    if ($e->isRateLimitError()) {
        $retryAfter = $e->getRetryAfter();
        echo "Rate limited. Retry after {$retryAfter} seconds.\n";
    }
    echo "Request ID: " . $e->getRequestId() . "\n";
}
```

---

## 🌐 HTTP Utilities

### HttpClient

HTTP client implementation with Adobe-specific features.

**Namespace**: `GrimReapper\PdfServices\Http\HttpClient`

#### Constructor

```php
public function __construct(PdfServicesConfig $config)
```

#### Methods

```php
public function sendRequest(
    string $method,
    string $url,
    array $headers = [],
    ?string $body = null
): HttpResponse

public function setTimeout(int $seconds): self
public function getTimeout(): int
public function setRetryAttempts(int $attempts): self
public function getRetryAttempts(): int
```

---

## 🛠️ Utilities

### FileUtils

File handling utilities.

**Namespace**: `GrimReapper\PdfServices\Utils\FileUtils`

#### Static Methods

```php
public static function validateFileExists(string $path): void
public static function validateFileReadable(string $path): void
public static function validateFileWritable(string $path): void
public static function getMimeType(string $path): string
public static function getFileSize(string $path): int
public static function cleanTempFiles(array $paths): void
```

---

*© 2024 Adobe. Adobe PDF Services SDK for PHP API Reference.*
