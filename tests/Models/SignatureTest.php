<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Tests\Models;

use GrimReapper\PdfServices\Models\Signature;
use PHPUnit\Framework\TestCase;
use DateTime;

class SignatureTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $signerName = 'John Doe';
        $signature = new Signature($signerName);

        $this->assertEquals($signerName, $signature->getSignerName());
        $this->assertNull($signature->getSigningTime());
        $this->assertFalse($signature->isValid());
        $this->assertNull($signature->getCertificateInfo());
        $this->assertNull($signature->getReason());
        $this->assertNull($signature->getLocation());
        $this->assertNull($signature->getContactInfo());
    }

    public function testConstructorWithAllParams(): void
    {
        $signerName = 'Jane Smith';
        $signingTime = new DateTime('2023-01-01T10:00:00Z');
        $isValid = true;
        $certificateInfo = ['issuer' => 'CA'];
        $reason = 'Approval';
        $location = 'Office';
        $contactInfo = 'jane@example.com';

        $signature = new Signature(
            $signerName,
            $signingTime,
            $isValid,
            $certificateInfo,
            $reason,
            $location,
            $contactInfo
        );

        $this->assertEquals($signerName, $signature->getSignerName());
        $this->assertEquals($signingTime, $signature->getSigningTime());
        $this->assertTrue($signature->isValid());
        $this->assertEquals($certificateInfo, $signature->getCertificateInfo());
        $this->assertEquals($reason, $signature->getReason());
        $this->assertEquals($location, $signature->getLocation());
        $this->assertEquals($contactInfo, $signature->getContactInfo());
    }

    public function testFromApiResponseMinimal(): void
    {
        $response = [
            'name' => 'API Signer'
        ];

        $signature = Signature::fromApiResponse($response);

        $this->assertEquals('API Signer', $signature->getSignerName());
        $this->assertNull($signature->getSigningTime());
        $this->assertFalse($signature->isValid());
        $this->assertNull($signature->getCertificateInfo());
        $this->assertNull($signature->getReason());
        $this->assertNull($signature->getLocation());
        $this->assertNull($signature->getContactInfo());
    }

    public function testFromApiResponseComplete(): void
    {
        $response = [
            'name' => 'Complete API Signer',
            'date' => '2023-01-01T10:00:00Z',
            'valid' => true,
            'certificate' => ['issuer' => 'Test CA', 'serialNumber' => '123'],
            'reason' => 'Contract Signing',
            'location' => 'Virtual Office',
            'contactInfo' => 'signer@example.com'
        ];

        $signature = Signature::fromApiResponse($response);

        $this->assertEquals('Complete API Signer', $signature->getSignerName());
        $this->assertInstanceOf(DateTime::class, $signature->getSigningTime());
        $this->assertTrue($signature->isValid());
        $this->assertEquals(['issuer' => 'Test CA', 'serialNumber' => '123'], $signature->getCertificateInfo());
        $this->assertEquals('Contract Signing', $signature->getReason());
        $this->assertEquals('Virtual Office', $signature->getLocation());
        $this->assertEquals('signer@example.com', $signature->getContactInfo());
    }

    public function testFromApiResponseInvalidDate(): void
    {
        $response = [
            'name' => 'Test',
            'date' => null,
            'valid' => false
        ];

        $signature = Signature::fromApiResponse($response);

        $this->assertNull($signature->getSigningTime());
        $this->assertFalse($signature->isValid());
    }
}
