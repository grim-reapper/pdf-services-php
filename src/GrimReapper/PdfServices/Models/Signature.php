<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Models;

use DateTime;
use DateTimeInterface;

/**
 * Represents a digital signature in a PDF
 */
class Signature
{
    private string $signerName;
    private ?DateTimeInterface $signingTime;
    private bool $isValid;
    private ?array $certificateInfo;
    private ?string $reason;
    private ?string $location;
    private ?string $contactInfo;

    /**
     * Create a new signature
     *
     * @param string $signerName The name of the signer
     * @param DateTimeInterface|null $signingTime When the document was signed
     * @param bool $isValid Whether the signature is valid
     * @param array|null $certificateInfo Certificate information
     * @param string|null $reason The signing reason
     * @param string|null $location The signing location
     * @param string|null $contactInfo Contact information
     */
    public function __construct(
        string $signerName,
        ?DateTimeInterface $signingTime = null,
        bool $isValid = false,
        ?array $certificateInfo = null,
        ?string $reason = null,
        ?string $location = null,
        ?string $contactInfo = null
    ) {
        $this->signerName = $signerName;
        $this->signingTime = $signingTime;
        $this->isValid = $isValid;
        $this->certificateInfo = $certificateInfo;
        $this->reason = $reason;
        $this->location = $location;
        $this->contactInfo = $contactInfo;
    }

    /**
     * Get the signer name
     *
     * @return string
     */
    public function getSignerName(): string
    {
        return $this->signerName;
    }

    /**
     * Get the signing time
     *
     * @return DateTimeInterface|null
     */
    public function getSigningTime(): ?DateTimeInterface
    {
        return $this->signingTime;
    }

    /**
     * Check if the signature is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Get certificate information
     *
     * @return array|null
     */
    public function getCertificateInfo(): ?array
    {
        return $this->certificateInfo;
    }

    /**
     * Get the signing reason
     *
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Get the signing location
     *
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * Get contact information
     *
     * @return string|null
     */
    public function getContactInfo(): ?string
    {
        return $this->contactInfo;
    }

    /**
     * Create signature from API response
     *
     * @param array $response The API response data
     * @return self
     */
    public static function fromApiResponse(array $response): self
    {
        $signingTime = isset($response['date']) ? new DateTime($response['date']) : null;

        return new self(
            $response['name'] ?? '',
            $signingTime,
            $response['valid'] ?? false,
            $response['certificate'] ?? null,
            $response['reason'] ?? null,
            $response['location'] ?? null,
            $response['contactInfo'] ?? null
        );
    }
}
