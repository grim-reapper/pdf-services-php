<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Services;

use GrimReapper\PdfServices\Models\Document;

/**
 * Service for PDF extraction
 */
class ExtractService extends AbstractService
{
    /**
     * Get the service name
     *
     * @return string
     */
    public function getServiceName(): string
    {
        return 'extractpdf';
    }

    /**
     * Extract content from a PDF
     *
     * @param string $filePath
     * @param array $elements List of elements to extract (text, tables, figures)
     * @param array $options Additional options (getCharBounds, includeStyling)
     * @return Document ZIP archive containing JSON and renditions
     */
    public function extract(string $filePath, array $elements = ['text'], array $options = []): Document
    {
        $this->validateFile($filePath);
        $content = file_get_contents($filePath);
        $asset = $this->uploadAsset($content, 'application/pdf');

        $requestData = [
            'assetID' => $asset['assetID'],
            'elementsToExtract' => $elements,
            'json' => '{}'
        ];

        if (isset($options['getCharBounds'])) $requestData['getCharBounds'] = (bool)$options['getCharBounds'];
        if (isset($options['includeStyling'])) $requestData['includeStyling'] = (bool)$options['includeStyling'];
        if (isset($options['tableOutputFormat'])) $requestData['tableOutputFormat'] = $options['tableOutputFormat'];
        if (isset($options['renditionsToExtract'])) $requestData['renditionsToExtract'] = $options['renditionsToExtract'];

        $response = $this->makeRequest('POST', '/operation/extractpdf', $requestData);
        $jobResult = $this->pollJob($response['location']);

        $assetData = $this->getResultData($jobResult, 'content');
        $resultContent = $this->httpClient->download($assetData['downloadUri']);

        return new Document(
            base64_encode($resultContent),
            'application/zip',
            'extract.zip',
            strlen($resultContent)
        );
    }
}
