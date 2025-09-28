<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Tests\Models;

use GrimReapper\PdfServices\Models\Document;
use GrimReapper\PdfServices\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStream;

class DocumentTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $document = new Document('test content', 'text/plain');

        $this->assertEquals('test content', $document->getContent());
        $this->assertEquals('text/plain', $document->getMimeType());
        $this->assertNull($document->getFilename());
        $this->assertNull($document->getSize());
        $this->assertFalse($document->isPdf());
    }

    public function testConstructorWithAllParams(): void
    {
        $document = new Document('content', 'application/pdf', 'test.pdf', 100);

        $this->assertEquals('content', $document->getContent());
        $this->assertEquals('application/pdf', $document->getMimeType());
        $this->assertEquals('test.pdf', $document->getFilename());
        $this->assertEquals(100, $document->getSize());
        $this->assertTrue($document->isPdf());
    }

    public function testCreateFromString(): void
    {
        $content = '<html><body>Hello World</body></html>';
        $document = Document::fromString($content, 'text/html', 'test.html');

        $this->assertEquals($content, $document->getContent());
        $this->assertEquals('text/html', $document->getMimeType());
        $this->assertEquals('test.html', $document->getFilename());
        $this->assertEquals(strlen($content), $document->getSize());
        $this->assertFalse($document->isPdf());
    }

    public function testCreateFromFile(): void
    {
        // Create a virtual file system
        $vfs = vfsStream::setup('root', null, [
            'test.html' => '<html><body>Test Content</body></html>'
        ]);

        $filePath = $vfs->url() . '/test.html';
        $document = Document::fromFile($filePath);

        $this->assertEquals('<html><body>Test Content</body></html>', $document->getContent());
        $this->assertEquals('text/html', $document->getMimeType());
        $this->assertEquals('test.html', $document->getFilename());
        $this->assertIsInt($document->getSize());
        $this->assertFalse($document->isPdf());
    }

    public function testCreateFromFileNonExistent(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File does not exist');

        Document::fromFile('/non/existent/file.html');
    }

    public function testSaveTo(): void
    {
        $vfs = vfsStream::setup('root');

        // For PDF (binary) content, we need to base64 encode it first
        $content = 'Test PDF content';
        $base64Content = base64_encode($content);
        $document = new Document($base64Content, 'application/pdf', 'test.pdf', strlen($base64Content));

        $savePath = $vfs->url() . '/output.pdf';
        $result = $document->saveTo($savePath);

        $this->assertTrue($result);
        $this->assertFileExists($savePath);
        $this->assertEquals($content, file_get_contents($savePath));
    }

    public function testSaveToCreatesDirectory(): void
    {
        $vfs = vfsStream::setup('root');

        $content = 'Test content';
        $document = new Document($content, 'text/plain');

        $savePath = $vfs->url() . '/subdir/output.txt';
        $result = $document->saveTo($savePath);

        $this->assertTrue($result);
        $this->assertFileExists($savePath);
        $this->assertDirectoryExists($vfs->url() . '/subdir');
    }

    public function testSaveToEmptyPath(): void
    {
        $document = new Document('content', 'text/plain');

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('File path cannot be empty');

        $document->saveTo('');
    }

    public function testIsPdf(): void
    {
        $pdfDocument = new Document('PDF content', 'application/pdf');
        $htmlDocument = new Document('<html></html>', 'text/html');

        $this->assertTrue($pdfDocument->isPdf());
        $this->assertFalse($htmlDocument->isPdf());
    }

    public function testBinaryContentHandling(): void
    {
        // Test binary content (simulated with base64)
        $binaryContent = base64_encode('binary pdf content');
        $document = new Document($binaryContent, 'application/pdf');

        // The content should be returned as-is (base64 encoded)
        $this->assertEquals($binaryContent, $document->getContent());
    }
}
