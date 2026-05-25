<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;
use GrimReapper\PdfServices\Models\Job;

/**
 * Service for converting documents to and from PDF
 */
class PdfConversionService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'pdf-conversion';
    }

    /**
     * Convert a DOCX file to PDF
     *
     * @param string $filePath Path to the DOCX file
     * @return Document The converted PDF document
     */
    public function docxToPdf(string $filePath): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent(),
                'format' => 'docx'
            ],
            'output' => ['format' => 'pdf']
        ];

        $response = $this->makeRequest('POST', '/pdf-conversion/docx-to-pdf', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'output.pdf',
            $response['size'] ?? null
        );
    }

    /**
     * Convert a PDF file to DOCX
     *
     * @param string $filePath Path to the PDF file
     * @return Document The converted DOCX document
     */
    public function pdfToDocx(string $filePath): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent(),
                'format' => 'pdf'
            ],
            'output' => ['format' => 'docx']
        ];

        $response = $this->makeRequest('POST', '/pdf-conversion/pdf-to-docx', $data);

        return new Document(
            $response['content'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $response['filename'] ?? 'output.docx',
            $response['size'] ?? null
        );
    }

    /**
     * Convert an image to PDF
     *
     * @param string $filePath Path to the image file
     * @return Document The converted PDF document
     */
    public function imageToPdf(string $filePath): Document
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent(),
                'format' => pathinfo($filePath, PATHINFO_EXTENSION)
            ],
            'output' => ['format' => 'pdf']
        ];

        $response = $this->makeRequest('POST', '/pdf-conversion/image-to-pdf', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'output.pdf',
            $response['size'] ?? null
        );
    }

    /**
     * Start an asynchronous DOCX to PDF conversion
     *
     * @param string $filePath Path to the DOCX file
     * @return Job The started job
     */
    public function docxToPdfAsync(string $filePath): Job
    {
        $this->validateFile($filePath);
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent(),
                'format' => 'docx'
            ],
            'output' => ['format' => 'pdf']
        ];

        $response = $this->makeRequest('POST', '/pdf-conversion/docx-to-pdf/async', $data);

        return Job::fromApiResponse($response);
    }

    /**
     * Get the status of a conversion job
     *
     * @param string $jobId The job ID
     * @return Job The job status
     */
    public function getJobStatus(string $jobId): Job
    {
        $response = $this->makeRequest('GET', "/pdf-conversion/jobs/{$jobId}");
        return Job::fromApiResponse($response);
    }

    /**
     * Get the result of a completed conversion job
     *
     * @param string $jobId The job ID
     * @return Document The result document
     */
    public function getJobResult(string $jobId): Document
    {
        $response = $this->makeRequest('GET', "/pdf-conversion/jobs/{$jobId}/result");

        return new Document(
            $response['content'],
            $response['mimeType'] ?? 'application/pdf',
            $response['filename'] ?? 'output.pdf',
            $response['size'] ?? null
        );
    }
}
