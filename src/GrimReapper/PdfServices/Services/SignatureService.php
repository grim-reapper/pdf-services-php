<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for digital signatures
 */
class SignatureService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'signature';
    }

    /**
     * Add a signature field to a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Signature field options
     * @return Document The PDF document with the added signature field
     */
    public function addSignatureField(string $filePath, array $options): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'options' => $options,
        ];

        $response = $this->makeRequest('POST', '/operation/pdfeseal', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            $resultContent,
            'application/pdf',
            'signature_field.pdf',
            strlen($resultContent)
        );
    }

    /**
     * Add a digital signature to a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Signature options
     * @return Document The signed PDF document
     */
    public function addSignature(string $filePath, array $options): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'options' => $options,
        ];

        $response = $this->makeRequest('POST', '/operation/pdfeseal', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            $resultContent,
            'application/pdf',
            'signed.pdf',
            strlen($resultContent)
        );
    }
}
