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
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $data = ['assetID' => $asset['assetID']];
        $response = $this->makeRequest('POST', '/operation/createpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'output.pdf',
            strlen($resultContent)
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
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'targetFormat' => 'docx'
        ];
        $response = $this->makeRequest('POST', '/operation/exportpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'output.docx',
            strlen($resultContent)
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
        $content = file_get_contents($filePath);
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mediaType = 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);

        $asset = $this->uploadAsset($content, $mediaType);

        $data = ['assetID' => $asset['assetID']];
        $response = $this->makeRequest('POST', '/operation/createpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $resultContent = $this->httpClient->download($jobResult['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'output.pdf',
            strlen($resultContent)
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
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        $data = ['assetID' => $asset['assetID']];
        $response = $this->makeRequest('POST', '/operation/createpdf', $data);

        return new Job($response['location'], 'in_progress');
    }

    /**
     * Get the status of a conversion job
     *
     * @param string $jobId The job ID (location URL)
     * @return Job The job status
     */
    public function getJobStatus(string $jobId): Job
    {
        $response = $this->httpClient->request('GET', $jobId, [], [], true);
        return Job::fromApiResponse($response);
    }

    /**
     * Get the result of a completed conversion job
     *
     * @param string $jobId The job ID (location URL)
     * @return Document The result document
     */
    public function getJobResult(string $jobId): Document
    {
        $response = $this->httpClient->request('GET', $jobId, [], [], true);
        if ($response['status'] !== 'done') {
            throw new \RuntimeException('Job is not completed yet');
        }

        $content = $this->httpClient->download($response['result']['asset']['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'output.pdf',
            strlen($content)
        );
    }
}
