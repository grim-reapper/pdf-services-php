<?php

declare(strict_types=1);

namespace GrimReapper\PdfServices\Tests\Models;

use GrimReapper\PdfServices\Models\Job;
use PHPUnit\Framework\TestCase;
use DateTime;

class JobTest extends TestCase
{
    public function testConstructorDefaults(): void
    {
        $jobId = 'test-job-123';
        $job = new Job($jobId);

        $this->assertEquals($jobId, $job->getJobId());
        $this->assertEquals('in_progress', $job->getStatus());
        $this->assertNull($job->getResult());
        $this->assertNull($job->getError());
        $this->assertInstanceOf(DateTime::class, $job->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $job->getUpdatedAt());
        $this->assertFalse($job->isCompleted());
        $this->assertTrue($job->isInProgress());
        $this->assertFalse($job->isFailed());
    }

    public function testConstructorWithAllParams(): void
    {
        $jobId = 'test-job-456';
        $status = 'done';
        $createdAt = new DateTime('2023-01-01T10:00:00Z');
        $updatedAt = new DateTime('2023-01-01T11:00:00Z');
        $result = ['key' => 'value'];
        $error = 'some error';

        $job = new Job($jobId, $status, $createdAt, $updatedAt, $result, $error);

        $this->assertEquals($jobId, $job->getJobId());
        $this->assertEquals('done', $job->getStatus());
        $this->assertEquals($result, $job->getResult());
        $this->assertEquals($error, $job->getError());
        $this->assertEquals($createdAt, $job->getCreatedAt());
        $this->assertEquals($updatedAt, $job->getUpdatedAt());
        $this->assertTrue($job->isCompleted());
        $this->assertFalse($job->isInProgress());
        $this->assertFalse($job->isFailed());
    }

    public function testIsCompleted(): void
    {
        $job = new Job('test-job');
        $this->assertFalse($job->isCompleted());

        $job = new Job('test-job', 'done');
        $this->assertTrue($job->isCompleted());
    }

    public function testIsInProgress(): void
    {
        $job = new Job('test-job');
        $this->assertTrue($job->isInProgress());

        $job = new Job('test-job', 'done');
        $this->assertFalse($job->isInProgress());
    }

    public function testIsFailed(): void
    {
        $job = new Job('test-job');
        $this->assertFalse($job->isFailed());

        $job = new Job('test-job', 'failed');
        $this->assertTrue($job->isFailed());
    }

    public function testUpdateStatus(): void
    {
        $job = new Job('test-job');
        $firstUpdatedAt = $job->getUpdatedAt();

        sleep(1); // to ensure different timestamps

        $updatedJob = $job->updateStatus('done', ['result' => 'data'], null);

        $this->assertSame($job, $updatedJob); // should return self
        $this->assertEquals('done', $job->getStatus());
        $this->assertEquals(['result' => 'data'], $job->getResult());
        $this->assertNull($job->getError());
        $this->assertGreaterThan($firstUpdatedAt, $job->getUpdatedAt());
    }

    public function testUpdateStatusWithError(): void
    {
        $job = new Job('test-job');
        $job->updateStatus('failed', null, 'Error message');

        $this->assertEquals('failed', $job->getStatus());
        $this->assertNull($job->getResult());
        $this->assertEquals('Error message', $job->getError());
    }

    public function testFromApiResponseMinimal(): void
    {
        $response = [
            'jobId' => 'api-job-123'
        ];

        $job = Job::fromApiResponse($response);

        $this->assertEquals('api-job-123', $job->getJobId());
        $this->assertEquals('in_progress', $job->getStatus());
        $this->assertNull($job->getResult());
        $this->assertNull($job->getError());
        $this->assertInstanceOf(DateTime::class, $job->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $job->getUpdatedAt());
    }

    public function testFromApiResponseComplete(): void
    {
        $response = [
            'jobId' => 'api-job-789',
            'status' => 'done',
            'created' => '2023-01-01T10:00:00Z',
            'modified' => '2023-01-01T11:00:00Z',
            'result' => ['data' => 'job result data'],
            'error' => null
        ];

        $job = Job::fromApiResponse($response);

        $this->assertEquals('api-job-789', $job->getJobId());
        $this->assertEquals('done', $job->getStatus());
        $this->assertEquals(['data' => 'job result data'], $job->getResult());
        $this->assertNull($job->getError());
        $this->assertInstanceOf(DateTime::class, $job->getCreatedAt());
        $this->assertInstanceOf(DateTime::class, $job->getUpdatedAt());
    }

    public function testFromApiResponseWithError(): void
    {
        $response = [
            'jobId' => 'api-job-000',
            'status' => 'failed',
            'error' => 'API error message'
        ];

        $job = Job::fromApiResponse($response);

        $this->assertEquals('api-job-000', $job->getJobId());
        $this->assertEquals('failed', $job->getStatus());
        $this->assertEquals('API error message', $job->getError());
        $this->assertNull($job->getResult());
        $this->assertTrue($job->isFailed());
    }
}
