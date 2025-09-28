<?php

declare(strict_types=1);

/**
 * Basic usage example for Adobe PDF Services PHP SDK
 *
 * This example demonstrates how to:
 * - Set up the PDF Services client
 * - Create a PDF from HTML
 * - Convert a DOCX file to PDF
 * - Merge multiple PDFs
 * - Add a digital signature
 * - Compare two PDFs
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use GrimReapper\PdfServices\Client;
use GrimReapper\PdfServices\Config\PdfServicesConfig;
use GrimReapper\PdfServices\Exceptions\PdfServicesException;

function main(): void
{
    try {
        // Configuration
        // In a real application, use environment variables or secure config
        $config = new PdfServicesConfig(
            apiKey: getenv('ADOBE_PDF_SERVICES_API_KEY') ?: 'your-api-key',
            clientId: getenv('ADOBE_PDF_SERVICES_CLIENT_ID') ?: 'your-client-id',
            organizationId: getenv('ADOBE_PDF_SERVICES_ORGANIZATION_ID') ?: 'your-organization-id'
        );

        // Create the client
        $client = new Client($config);

        echo "Adobe PDF Services PHP SDK - Basic Usage Example\n";
        echo "================================================\n\n";

        // Example 1: Create PDF from HTML
        echo "1. Creating PDF from HTML...\n";

        $creationService = $client->createPdf();
        $htmlContent = '
            <html>
            <head><title>Sample Document</title></head>
            <body>
                <h1>Hello World</h1>
                <p>This PDF was created using Adobe PDF Services PHP SDK.</p>
                <ul>
                    <li>Easy to use</li>
                    <li>Comprehensive features</li>
                    <li>Production ready</li>
                </ul>
            </body>
            </html>
        ';

        $result = $creationService->fromHtml($htmlContent, [
            'format' => 'A4',
            'margin' => ['top' => '1in', 'bottom' => '1in']
        ]);

        $result->saveTo('output/html-to-pdf.pdf');
        echo "✓ PDF created from HTML: output/html-to-pdf.pdf\n\n";

        // Example 2: PDF Conversion (if you have a DOCX file)
        echo "2. PDF Conversion example...\n";

        $conversionService = $client->convert();

        // This would work if you have a DOCX file
        // $result = $conversionService->docxToPdf('input/sample.docx');
        // $result->saveTo('output/docx-to-pdf.pdf');
        // echo "✓ DOCX converted to PDF: output/docx-to-pdf.pdf\n";

        echo "✓ PDF conversion service ready (add a DOCX file to test)\n\n";

        // Example 3: PDF Merging
        echo "3. PDF Merging example...\n";

        $mergeService = $client->merge();

        // Create some sample PDFs to merge
        $samplePdf1 = $creationService->fromHtml('<h1>Document 1</h1><p>First document content.</p>');
        $samplePdf1->saveTo('temp/doc1.pdf');

        $samplePdf2 = $creationService->fromHtml('<h1>Document 2</h1><p>Second document content.</p>');
        $samplePdf2->saveTo('temp/doc2.pdf');

        // Merge the PDFs
        $mergedResult = $mergeService->combine([
            'temp/doc1.pdf',
            'temp/doc2.pdf'
        ]);

        $mergedResult->saveTo('output/merged.pdf');
        echo "✓ PDFs merged: output/merged.pdf\n\n";

        // Example 4: Digital Signatures
        echo "4. Digital signatures example...\n";

        $signatureService = $client->signature();

        // Add a signature field to the merged PDF
        $signedResult = $signatureService->addSignatureField('output/merged.pdf', [
            'name' => 'approval_signature',
            'position' => ['x' => 100, 'y' => 100, 'width' => 200, 'height' => 50],
            'page' => 1,
            'required' => true
        ]);

        $signedResult->saveTo('output/with-signature-field.pdf');
        echo "✓ Signature field added: output/with-signature-field.pdf\n";
        echo "  (Note: Actual signing requires a digital certificate)\n\n";

        // Example 5: PDF Comparison
        echo "5. PDF comparison example...\n";

        $comparisonService = $client->compare();

        // Create two slightly different PDFs
        $pdfV1 = $creationService->fromHtml('<h1>Version 1</h1><p>Original content.</p>');
        $pdfV1->saveTo('temp/version1.pdf');

        $pdfV2 = $creationService->fromHtml('<h1>Version 2</h1><p>Modified content.</p>');
        $pdfV2->saveTo('temp/version2.pdf');

        // Compare the PDFs
        $comparisonResult = $comparisonService->comparePdfs(
            'temp/version1.pdf',
            'temp/version2.pdf'
        );

        echo "✓ PDF comparison completed\n";
        echo "  Documents identical: " . ($comparisonResult->areIdentical() ? 'Yes' : 'No') . "\n";
        echo "  Differences found: " . $comparisonResult->getDifferenceCount() . "\n\n";

        // Generate a diff report
        $diffReport = $comparisonService->generateDiffReport(
            'temp/version1.pdf',
            'temp/version2.pdf',
            'output/diff-report.pdf'
        );
        echo "✓ Diff report generated: output/diff-report.pdf\n\n";

        echo "All examples completed successfully!\n";
        echo "Check the 'output' directory for generated files.\n";

        // Cleanup temporary files
        @unlink('temp/doc1.pdf');
        @unlink('temp/doc2.pdf');
        @unlink('temp/version1.pdf');
        @unlink('temp/version2.pdf');
        @rmdir('temp');

    } catch (PdfServicesException $e) {
        echo "PDF Services Error: " . $e->getMessage() . "\n";
        if ($e->getRequestId()) {
            echo "Request ID: " . $e->getRequestId() . "\n";
        }
        exit(1);
    } catch (Exception $e) {
        echo "General Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// Ensure output directory exists
if (!is_dir('output')) {
    mkdir('output', 0755, true);
}
if (!is_dir('temp')) {
    mkdir('temp', 0755, true);
}

// Run the example
main();
