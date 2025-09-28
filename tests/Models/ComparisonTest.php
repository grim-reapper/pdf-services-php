<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Tests\Models;

use GrimReapper\PdfServices\Models\Comparison;
use PHPUnit\Framework\TestCase;

class ComparisonTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $comparison = new Comparison('text', 1, 'Text changed');

        $this->assertEquals('text', $comparison->getType());
        $this->assertEquals(1, $comparison->getPageNumber());
        $this->assertEquals('Text changed', $comparison->getDescription());
        $this->assertEquals([], $comparison->getPosition());
        $this->assertNull($comparison->getOldValue());
        $this->assertNull($comparison->getNewValue());
        $this->assertTrue($comparison->isTextDifference());
        $this->assertFalse($comparison->isVisualDifference());
        $this->assertFalse($comparison->isStructuralDifference());
    }

    public function testConstructorWithAllParams(): void
    {
        $comparison = new Comparison(
            'visual',
            2,
            'Image difference',
            [10, 20, 50, 30],
            'old text',
            'new text'
        );

        $this->assertEquals('visual', $comparison->getType());
        $this->assertEquals(2, $comparison->getPageNumber());
        $this->assertEquals('Image difference', $comparison->getDescription());
        $this->assertEquals([10, 20, 50, 30], $comparison->getPosition());
        $this->assertEquals('old text', $comparison->getOldValue());
        $this->assertEquals('new text', $comparison->getNewValue());
        $this->assertFalse($comparison->isTextDifference());
        $this->assertTrue($comparison->isVisualDifference());
        $this->assertFalse($comparison->isStructuralDifference());
    }

    public function testIsStructuralDifference(): void
    {
        $comparison = new Comparison('structural', 1, 'Structure changed');
        $this->assertTrue($comparison->isStructuralDifference());
        $this->assertFalse($comparison->isTextDifference());
        $this->assertFalse($comparison->isVisualDifference());
    }

    public function testFromApiResponseMinimal(): void
    {
        $response = [
            'type' => 'text',
            'pageNumber' => 3,
            'description' => 'API difference'
        ];

        $comparison = Comparison::fromApiResponse($response);

        $this->assertEquals('text', $comparison->getType());
        $this->assertEquals(3, $comparison->getPageNumber());
        $this->assertEquals('API difference', $comparison->getDescription());
        $this->assertEquals([], $comparison->getPosition());
        $this->assertNull($comparison->getOldValue());
        $this->assertNull($comparison->getNewValue());
    }

    public function testFromApiResponseComplete(): void
    {
        $response = [
            'type' => 'visual',
            'pageNumber' => 5,
            'description' => 'Color change',
            'position' => [100, 200],
            'oldValue' => 'red',
            'newValue' => 'blue'
        ];

        $comparison = Comparison::fromApiResponse($response);

        $this->assertEquals('visual', $comparison->getType());
        $this->assertEquals(5, $comparison->getPageNumber());
        $this->assertEquals('Color change', $comparison->getDescription());
        $this->assertEquals([100, 200], $comparison->getPosition());
        $this->assertEquals('red', $comparison->getOldValue());
        $this->assertEquals('blue', $comparison->getNewValue());
    }

    public function testFromApiResponseDefaults(): void
    {
        $response = [];
        $comparison = Comparison::fromApiResponse($response);

        $this->assertEquals('unknown', $comparison->getType());
        $this->assertEquals(1, $comparison->getPageNumber());
        $this->assertEquals('', $comparison->getDescription());
    }
}
