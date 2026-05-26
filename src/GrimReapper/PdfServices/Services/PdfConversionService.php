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
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'output.pdf',
            strlen($resultContent)
        );
    }

    /**
     * Export a PDF file to another format (DOCX, XLSX, PPTX, RTF, JPEG, PNG)
     *
     * @param string $filePath Path to the PDF file
     * @param string $targetFormat Target format (docx, xlsx, pptx, rtf, jpeg, png)
     * @return Document|array A single Document or an array of Documents (for images)
     */
    public function export(string $filePath, string $targetFormat = 'docx'): Document|array
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $targetFormat = strtolower($targetFormat);
        $data = [
            'assetID' => $asset['assetID'],
            'targetFormat' => $targetFormat
        ];

        $response = $this->makeRequest('POST', '/operation/exportpdf', $data);
        $jobResult = $this->pollJob($response['location']);

        $mimeMap = [
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'rtf'  => 'application/rtf',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png'
        ];

        if (in_array($targetFormat, ['jpeg', 'png'])) {
            $assetsData = $this->getResultData($jobResult, 'assets');
            $results = [];
            foreach ($assetsData as $i => $assetData) {
                $resultContent = $this->httpClient->download($assetData['downloadUri']);
                $results[] = new Document(
                    base64_encode($resultContent),
                    $mimeMap[$targetFormat] ?? 'application/octet-stream',
                    "output_{$i}.{$targetFormat}",
                    strlen($resultContent)
                );
            }
            return $results;
        }

        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            $mimeMap[$targetFormat] ?? 'application/octet-stream',
            "output.{$targetFormat}",
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
        return $this->export($filePath, 'docx');
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
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

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

        return Job::fromApiResponse($response);
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
        return Job::fromApiResponse($response, $jobId);
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
        if (($response['status'] ?? '') !== 'done' && ($response['status'] ?? '') !== 'completed') {
            throw new \RuntimeException('Job is not completed yet');
        }

        $assetData = $this->getResultData($response, 'asset');
        $content = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($content),
            'application/pdf',
            'output.pdf',
            strlen($content)
        );
    }
}
