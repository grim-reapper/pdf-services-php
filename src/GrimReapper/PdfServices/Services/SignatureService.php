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
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/signature/add-field', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'signature_field.pdf',
            $response['size'] ?? null
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
        $document = Document::fromFile($filePath);

        $data = [
            'input' => [
                'content' => $document->getContent()
            ],
            'options' => $options
        ];

        $response = $this->makeRequest('POST', '/signature/add-signature', $data);

        return new Document(
            $response['content'],
            'application/pdf',
            $response['filename'] ?? 'signed.pdf',
            $response['size'] ?? null
        );
    }
}
