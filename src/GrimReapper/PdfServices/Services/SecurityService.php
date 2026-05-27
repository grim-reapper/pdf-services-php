<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF security and protection
 */
class SecurityService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'protectpdf';
    }

    /**
     * Protect a PDF document with password or permissions
     *
     * @param string $filePath Path to the PDF file
     * @param array $options Security options
     * @return Document The protected PDF document
     */
    public function protect(string $filePath, array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $protection = [];
        if (isset($options['password'])) {
            $protection['userPassword'] = $options['password'];
        }

        if (isset($options['ownerPassword'])) {
            $protection['ownerPassword'] = $options['ownerPassword'];
        }

        // Map permission enums to Adobe API v2 expected values
        $permissions = $options['permissions'] ?? [];
        $mappedPermissions = [];
        $permissionMap = [
            'PRINT_LOW_RES' => 'PRINT_LOW_QUALITY',
            'PRINT_HIGH_RES' => 'PRINT_HIGH_QUALITY',
            'PRINT_LOW_QUALITY' => 'PRINT_LOW_QUALITY',
            'PRINT_HIGH_QUALITY' => 'PRINT_HIGH_QUALITY',
            'EDIT_CONTENT' => 'EDIT_CONTENT',
            'COPY_CONTENT' => 'COPY_CONTENT',
            'EDIT_ANNOTATIONS' => 'EDIT_ANNOTATIONS',
            'EDIT_FORMS' => 'EDIT_FILL_AND_SIGN_FORM_FIELDS',
            'EDIT_FILL_AND_SIGN_FORM_FIELDS' => 'EDIT_FILL_AND_SIGN_FORM_FIELDS',
        ];

        foreach ($permissions as $perm) {
            $mappedPermissions[] = $permissionMap[strtoupper((string)$perm)] ?? $perm;
        }

        if (!empty($mappedPermissions)) {
            $protection['permissions'] = $mappedPermissions;
        }

        $data = [
            'assetID' => $asset['assetID'],
            'encryptionAlgorithm' => $options['encryptionAlgorithm'] ?? 'AES_256',
            'passwordProtection' => $protection
        ];

        $response = $this->makeRequest('POST', '/operation/protectpdf', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'protected.pdf',
            strlen($resultContent)
        );
    }

    /**
     * Remove protection from a PDF document
     *
     * @param string $filePath Path to the PDF file
     * @param string $password The owner password
     * @return Document The unprotected PDF document
     */
    public function unprotect(string $filePath, string $password): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $data = [
            'assetID' => $asset['assetID'],
            'password' => $password
        ];

        $response = $this->makeRequest('POST', '/operation/removeprotection', $data);
        $jobResult = $this->pollJob($response['location']);
        $assetData = $this->getResultData($jobResult, 'asset');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/pdf',
            'unprotected.pdf',
            strlen($resultContent)
        );
    }
}
