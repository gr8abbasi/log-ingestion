<?php

declare(strict_types=1);

namespace Tests\Unit\Interface\Http\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Interface\Http\Controller\LogController;
use Application\Log\Service\LogQueryServiceInterface;
use Interface\Http\Request\LogRequest;
use Interface\Http\Response\ApiResponseFactory;
use Domain\Log\ValueObject\LogFilters; // Using actual class here instead of mock

class LogControllerTest extends TestCase
{
    private LogController $controller;
    private MockObject&LogQueryServiceInterface $mockLogService;

    protected function setUp(): void
    {
        // Create a mock of the LogServiceInterface
        $this->mockLogService = $this->createMock(LogQueryServiceInterface::class);
        // Instantiate the controller with the mocked service
        $this->controller = new LogController($this->mockLogService);
    }


    public function testCountSuccess()
    {
        $logRequest = $this->createMock(LogRequest::class);
        $filters = new LogFilters(['service1'], 200, new \DateTimeImmutable(), new \DateTimeImmutable());

        $logRequest->method('toFilters')->willReturn($filters);

        $this->mockLogService->method('countLogs')->willReturn(10);

        $response = $this->controller->count($logRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('filters', $responseData['data']);
        $this->assertArrayHasKey('count', $responseData['data']);
        $this->assertEquals(10, $responseData['data']['count']);
    }

    public function testCountError()
    {
        $logRequest = $this->createMock(LogRequest::class);
        $filters = new LogFilters(['service1'], 200, new \DateTimeImmutable(), new \DateTimeImmutable());

        $logRequest->method('toFilters')->willReturn($filters);

        $this->mockLogService->method('countLogs')
            ->will($this->throwException(new \RuntimeException('Something went wrong', 500)));

        $response = $this->controller->count($logRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Something went wrong', $responseData['message']);
    }

    public function testCountWithInvalidLogRequest()
    {
        $logRequest = $this->createMock(LogRequest::class);
        $filters = new LogFilters(['service1'], 200, new \DateTimeImmutable(), new \DateTimeImmutable());

        $logRequest->method('toFilters')->willReturn($filters);

        $this->mockLogService->method('countLogs')
            ->will($this->throwException(new BadRequestHttpException('Invalid request parameters')));

        $response = $this->controller->count($logRequest);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('status', $responseData);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Invalid request parameters', $responseData['message']);
    }
}


