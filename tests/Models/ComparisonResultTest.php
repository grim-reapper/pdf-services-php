<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Tests\Models;

use GrimReapper\PdfServices\Models\ComparisonResult;
use GrimReapper\PdfServices\Models\Comparison;
use PHPUnit\Framework\TestCase;

class ComparisonResultTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $result = new ComparisonResult();

        $this->assertTrue($result->areIdentical());
        $this->assertEquals(0, $result->getDifferenceCount());
        $this->assertEquals([], $result->getDifferences());
        $this->assertEquals([], $result->getVisualDifferences());
        $this->assertEquals([], $result->getTextDifferences());
    }

    public function testConstructorWithAllParams(): void
    {
        $differences = [new Comparison('text', 1, 'Test diff')];
        $visualDifferences = [new Comparison('visual', 2, 'Visual diff')];
        $textDifferences = [new Comparison('text', 1, 'Text diff')];

        $result = new ComparisonResult(
            false,
            3,
            $differences,
            $visualDifferences,
            $textDifferences
        );

        $this->assertFalse($result->areIdentical());
        $this->assertEquals(3, $result->getDifferenceCount());
        $this->assertEquals($differences, $result->getDifferences());
        $this->assertEquals($visualDifferences, $result->getVisualDifferences());
        $this->assertEquals($textDifferences, $result->getTextDifferences());
    }

    public function testAddDifference(): void
    {
        $result = new ComparisonResult();
        $textDiff = new Comparison('text', 1, 'Text difference');
        $visualDiff = new Comparison('visual', 2, 'Visual difference');

        $result->addDifference($textDiff);
        $result->addDifference($visualDiff);

        $this->assertFalse($result->areIdentical());
        $this->assertEquals(2, $result->getDifferenceCount());
        $this->assertCount(2, $result->getDifferences());
        $this->assertCount(1, $result->getTextDifferences());
        $this->assertCount(1, $result->getVisualDifferences());
        $this->assertEquals($textDiff, $result->getTextDifferences()[0]);
        $this->assertEquals($visualDiff, $result->getVisualDifferences()[0]);
    }

    public function testFromApiResponseIdentical(): void
    {
        $response = [
            'identical' => true,
            'differenceCount' => 0,
            'differences' => []
        ];

        $result = ComparisonResult::fromApiResponse($response);

        $this->assertTrue($result->areIdentical());
        $this->assertEquals(0, $result->getDifferenceCount());
        $this->assertEquals([], $result->getDifferences());
    }

    public function testFromApiResponseWithDifferences(): void
    {
        $response = [
            'identical' => false,
            'differences' => [
                [
                    'type' => 'text',
                    'pageNumber' => 1,
                    'description' => 'Text content differs',
                    'position' => [10, 20],
                    'oldValue' => 'old text',
                    'newValue' => 'new text'
                ],
                [
                    'type' => 'visual',
                    'pageNumber' => 3,
                    'description' => 'Image differs',
                    'position' => [100, 200, 50, 30],
                    'oldValue' => null,
                    'newValue' => null
                ]
            ]
        ];

        $result = ComparisonResult::fromApiResponse($response);

        $this->assertFalse($result->areIdentical());
        $this->assertEquals(2, $result->getDifferenceCount());
        $this->assertCount(2, $result->getDifferences());
        $this->assertCount(1, $result->getTextDifferences());
        $this->assertCount(1, $result->getVisualDifferences());

        $textDiff = $result->getTextDifferences()[0];
        $this->assertEquals('text', $textDiff->getType());
        $this->assertEquals(1, $textDiff->getPageNumber());
        $this->assertEquals('Text content differs', $textDiff->getDescription());
        $this->assertEquals([10, 20], $textDiff->getPosition());
        $this->assertEquals('old text', $textDiff->getOldValue());
        $this->assertEquals('new text', $textDiff->getNewValue());
    }

    public function testFromApiResponseEmpty(): void
    {
        $response = [];
        $result = ComparisonResult::fromApiResponse($response);

        $this->assertTrue($result->areIdentical());
        $this->assertEquals(0, $result->getDifferenceCount());
        $this->assertEquals([], $result->getDifferences());
    }
}
