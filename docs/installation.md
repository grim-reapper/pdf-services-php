# Installation and Setup

This guide covers the installation and initial setup of the Adobe PDF Services PHP SDK, including system requirements, dependency management, and basic configuration.

## 📋 System Requirements

Before installing the Adobe PDF Services PHP SDK, ensure your environment meets the following requirements:

### Minimum Requirements

- **PHP**: 8.1 or higher
- **Composer**: Latest stable version recommended
- **Memory**: 256MB minimum, 512MB recommended for large file processing
- **Storage**: Sufficient disk space for temporary files during processing

### Recommended Environment

- **PHP**: 8.2 or higher for best performance
- **Composer**: 2.x
- **Memory**: 1GB or more for batch processing
- **Storage**: SSD storage for improved I/O performance

### Operating System Support

- **Linux**: All major distributions (Ubuntu, CentOS, Debian, etc.)
- **macOS**: 10.15 or later
- **Windows**: Windows 10 or later (via WSL recommended)

## 🚀 Installation

### Composer Installation (Recommended)

The recommended way to install the Adobe PDF Services PHP SDK is through [Composer](https://getcomposer.org/), PHP's dependency manager.

#### Install Composer

If you don't have Composer installed, install it first:

```bash
# Linux/macOS
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Windows (PowerShell)
Invoke-WebRequest -Uri https://getcomposer.org/installer -OutFile composer-setup.php
php composer-setup.php
Move-Item composer.phar C:\bin\composer.phar
```

#### Add the SDK to Your Project

```bash
# Add to existing PHP project
composer require grim-reapper/pdf-services-php

# Or in composer.json
{
    "require": {
        "grim-reapper/pdf-services-php": "^1.0"
    }
}
```

#### Update Dependencies

After adding the package, update your project dependencies:

```bash
composer install
composer update
```

## 🛠️ Basic Setup

### 1. Include Autoloader

Add the Composer autoloader to your PHP scripts:

```php
<?php

require_once 'vendor/autoload.php';

// Your code here
```

### 2. Verify Installation

Create a simple test script to verify the installation:

```php
<?php

require_once 'vendor/autoload.php';

use GrimReapper\PdfServices\Models\Document;

try {
    // Test basic class availability
    $document = new Document('test content', 'text/plain');
    echo "✅ Adobe PDF Services PHP SDK installed successfully!\n";
    echo "Document created: " . get_class($document) . "\n";
} catch (Exception $e) {
    echo "❌ Installation verification failed: " . $e->getMessage() . "\n";
}
```

## 🔧 Configuration

### API Credentials

To use the Adobe PDF Services API, you need valid API credentials from your Adobe account.

#### Obtain Credentials

1. Visit [Adobe Developer Console](https://developer.adobe.com/console)
2. Sign in with your Adobe account
3. Create a new project or select an existing one
4. Add the "PDF Services API" to your project
5. Generate your API credentials:
   - **Client ID**: Your application's unique identifier
   - **Client Secret**: Private key for authentication
   - **Organization ID**: Your organization's unique identifier

#### Basic Configuration

```php
<?php

require_once 'vendor/autoload.php';

use GrimReapper\PdfServices\Client;
use GrimReapper\PdfServices\Config\PdfServicesConfig;

try {
    // Method 1: Direct configuration
    $config = new PdfServicesConfig(
        apiKey: 'your-api-key',
        clientId: 'your-client-id',
        organizationId: 'your-organization-id'
    );

    $client = new Client($config);
    echo "✅ Client configured successfully!\n";

} catch (Exception $e) {
    echo "❌ Configuration failed: " . $e->getMessage() . "\n";
}
```

#### Environment Variables Configuration

For better security and flexibility, configure credentials using environment variables:

```bash
# Set environment variables
export ADOBE_PDF_SERVICES_API_KEY="your-api-key"
export ADOBE_PDF_SERVICES_CLIENT_ID="your-client-id"
export ADOBE_PDF_SERVICES_ORGANIZATION_ID="your-organization-id"
export ADOBE_PDF_SERVICES_ENVIRONMENT="production"
```

```php
use GrimReapper\PdfServices\Config\PdfServicesConfig;

// Method 2: Environment-based configuration
$config = PdfServicesConfig::fromEnvironment();
$client = new Client($config);
```

#### Configuration File

For more complex applications, you can store configuration in external files:

```php
// config/pdf-services.php
return [
    'api_key' => 'your-api-key',
    'client_id' => 'your-client-id',
    'organization_id' => 'your-organization-id',
    'environment' => 'production',
    'timeout' => 30, // seconds
    'retry_attempts' => 3
];
```

```php
$configFile = require 'config/pdf-services.php';
$config = new PdfServicesConfig(
    apiKey: $configFile['api_key'],
    clientId: $configFile['client_id'],
    organizationId: $configFile['organization_id']
);
$client = new Client($config);
```

## 🌐 HTTP Client Configuration

### PSR-18 HTTP Client Support

The SDK supports custom PSR-18 compatible HTTP clients for advanced use cases:

```php
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\HttpFactory;
use GrimReapper\PdfServices\Config\PdfServicesConfig;

$guzzleClient = new GuzzleClient([
    'timeout' => 30,
    'headers' => [
        'User-Agent' => 'My-App/1.0'
    ]
]);

$requestFactory = new HttpFactory();
$streamFactory = new HttpFactory();

$config = new PdfServicesConfig('api-key', 'client-id', 'org-id');
$config->setHttpClient($guzzleClient);
$config->setRequestFactory($requestFactory);
$config->setStreamFactory($streamFactory);

$client = new Client($config);
```

### Custom HTTP Client Requirements

When using a custom HTTP client, ensure it implements:

- **PSR-18**: `Psr\Http\Client\ClientInterface`
- **PSR-17**: Request and Stream factories

### Proxy Support

```php
$guzzleClient = new GuzzleClient([
    'proxy' => [
        'http' => 'tcp://localhost:8125',
        'https' => 'tcp://localhost:8125',
        'no' => ['.local']
    ]
]);

$config = new PdfServicesConfig('api-key', 'client-id', 'org-id');
$config->setHttpClient($guzzleClient);
```

## 🔍 Verification Tests

### Complete Setup Verification Script

```php
<?php
/**
 * Adobe PDF Services Setup Verification
 *
 * Run this script to verify your SDK installation and configuration
 */

require_once 'vendor/autoload.php';

use GrimReapper\PdfServices\Client;
use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Models\Document;

echo "🔍 Adobe PDF Services PHP SDK - Setup Verification\n";
echo "==================================================\n\n";

$checks = [];
.errors = [];

// 1. Check PHP version
echo "1. Checking PHP version... ";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    $checks['php_version'] = true;
    echo "✅ PHP " . PHP_VERSION . "\n";
} else {
    $checks['php_version'] = false;
    $errors[] = "PHP 8.1.0 or higher required. Current: " . PHP_VERSION;
    echo "❌ PHP version too old\n";
}

// 2. Check required extensions
echo "2. Checking required extensions... ";
$requiredExtensions = ['mbstring', 'json', 'fileinfo'];
$missingExtensions = [];

foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $missingExtensions[] = $ext;
    }
}

if (empty($missingExtensions)) {
    $checks['extensions'] = true;
    echo "✅ All required extensions loaded\n";
} else {
    $checks['extensions'] = false;
    $errors[] = "Missing extensions: " . implode(', ', $missingExtensions);
    echo "❌ Missing extensions: " . implode(', ', $missingExtensions) . "\n";
}

// 3. Check class availability
echo "3. Checking SDK classes... ";
try {
    $document = new Document('test', 'text/plain');
    $config = new PdfServicesConfig('test', 'test', 'test');
    $checks['classes'] = true;
    echo "✅ SDK classes loaded\n";
} catch (Throwable $e) {
    $checks['classes'] = false;
    $errors[] = "SDK classes not available: " . $e->getMessage();
    echo "❌ SDK classes not loaded\n";
}

// 4. Check environment variables (optional)
echo "4. Checking environment variables... ";
$envVars = [
    'ADOBE_PDF_SERVICES_API_KEY',
    'ADOBE_PDF_SERVICES_CLIENT_ID',
    'ADOBE_PDF_SERVICES_ORGANIZATION_ID'
];

$envCount = 0;
foreach ($envVars as $var) {
    if (!empty(getenv($var))) {
        $envCount++;
    }
}

if ($envCount === 3) {
    $checks['environment'] = true;
    echo "✅ All environment variables set\n";
} elseif ($envCount > 0) {
    $checks['environment'] = 'partial';
    echo "⚠️  Some environment variables set ({$envCount}/3)\n";
} else {
    $checks['environment'] = false;
    echo "ℹ️  No environment variables set\n";
}

// 5. Check client instantiation
echo "5. Checking client configuration... ";
try {
    if ($checks['environment'] === true) {
        $config = PdfServicesConfig::fromEnvironment();
        $client = new Client($config);
        $checks['client_config'] = true;
        echo "✅ Client configured from environment\n";
    } else {
        // Test with dummy credentials (will fail authentication later)
        $config = new PdfServicesConfig('test', 'test', 'test');
        $client = new Client($config);
        $checks['client_config'] = true;
        echo "✅ Client instantiation works (dummy credentials)\n";
    }
} catch (Throwable $e) {
    $checks['client_config'] = false;
    $errors[] = "Client configuration failed: " . $e->getMessage();
    echo "❌ Client configuration failed\n";
}

echo "\n📊 Summary\n";
echo "=========\n";
$passed = count(array_filter($checks, fn($v) => $v === true));
$total = count($checks);
echo "Passed: {$passed}/{$total} checks\n\n";

if (!empty($errors)) {
    echo "❌ Issues found:\n";
    foreach ($errors as $error) {
        echo "   - " . $error . "\n";
    }
    echo "\n";
}

if ($passed === $total) {
    echo "🎉 Setup verification complete! Your environment is ready for Adobe PDF Services.\n\n";
    echo "Next steps:\n";
    echo "1. Set up your Adobe API credentials\n";
    echo "2. Check the Getting Started guide\n";
    echo "3. Review the examples\n";
} else {
    echo "⚠️  Setup verification complete with issues. Please resolve them before proceeding.\n\n";
    echo "For help, see:\n";
    echo "- Installation guide: https://developer.adobe.com/document-services/docs/overview/\n";
    echo "- GitHub repository: https://github.com/adobe/pdf-services-php\n";
}

echo "\n🔗 Useful links:\n";
echo "- Documentation: https://developer.adobe.com/document-services/docs/overview/\n";
echo "- API Reference: https://developer.adobe.com/document-services/apis/pdf-services/\n";
echo "- Developer Console: https://developer.adobe.com/console/\n";
```

### Run Verification

```bash
php verify-setup.php
```

## 🐛 Troubleshooting

### Common Installation Issues

#### 1. Class Not Found Errors

**Problem**: `Class 'GrimReapper\PdfServices\Client' not found`

**Solutions**:
```bash
# Clear Composer cache
composer clear-cache

# Remove vendor directory and reinstall
rm -rf vendor/
composer install

# Check autoloader
composer dump-autoload
```

#### 2. Memory Issues

**Problem**: `Fatal error: Allowed memory size exhausted`

**Solutions**:
```php
// Increase memory limit in php.ini or .htaccess
ini_set('memory_limit', '512M');

// Or in your script
memory_set_limit('512M');
```

#### 3. SSL Certificate Issues

**Problem**: `SSL certificate problem: unable to get local issuer certificate`

**Solutions**:
```php
// Disable SSL verification (not recommended for production)
$guzzleClient = new GuzzleClient(['verify' => false]);

// Or update CA certificates
// On Ubuntu/Debian:
sudo update-ca-certificates
# On CentOS/RHEL:
sudo yum update ca-certificates
```

### Composer Issues

#### Slow Downloads

```bash
# Use Chinese mirror (example)
composer config -g repositories.packagist composer https://mirrors.aliyun.com/composer/

# Use private repositories
composer config repositories.adobe private https://my-private-repo.com
```

## 🔒 Security Considerations

### API Credentials

- **Never commit API credentials to version control**
- Use environment variables or secure configuration files
- Rotate credentials regularly
- Use different credentials for development and production

### File Handling

- Validate uploaded file types and sizes
- Implement rate limiting for API calls
- Use secure file permissions (0644 for files, 0755 for directories)
- Clean up temporary files after processing

### Network Security

- Use HTTPS for all API communications
- Implement proper SSL/TLS verification
- Consider IP whitelisting if available

## 📚 Next Steps

After successful installation and setup:

1. **[Getting Started Guide](getting-started.md)**: Learn basic usage
2. **[Examples Directory](examples/)**: Explore detailed code samples
3. **[API Reference](api-reference.md)**: Understand all available features

---

*© 2024 Adobe. Adobe PDF Services SDK for PHP documentation.*
