<?php

declare(strict_types=1);

namespace Tests\Unit\Interface\Http\Response;

use Interface\Http\Response\ApiResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiResponseFactoryTest extends TestCase
{
    public function testSuccessResponse(): void
    {
        $data = ['foo' => 'bar'];
        $response = ApiResponseFactory::success($data);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals($data, $responseData['data']);
    }

    public function testErrorResponseWithHttpException(): void
    {
        $exception = new NotFoundHttpException('Not Found');
        $response = ApiResponseFactory::error($exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(404, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Not Found', $responseData['message']);
    }

    public function testErrorResponseWithGenericException(): void
    {
        $exception = new \RuntimeException('Unexpected failure');
        $response = ApiResponseFactory::error($exception);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(500, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('error', $responseData['status']);
        $this->assertEquals('Unexpected failure', $responseData['message']);
    }
}
