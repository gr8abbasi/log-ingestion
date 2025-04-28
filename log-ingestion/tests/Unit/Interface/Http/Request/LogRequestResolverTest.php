<?php

declare(strict_types=1);

namespace Tests\Unit\Interface\Http\Request;

use Interface\Http\Request\LogRequest;
use Interface\Http\Request\LogRequestResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class LogRequestResolverTest extends TestCase
{
    private ValidatorInterface $validatorMock;
    private LogRequestResolver $resolver;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(ValidatorInterface::class);
        $this->resolver = new LogRequestResolver($this->validatorMock);
    }

    public function testSupportsReturnsTrueForLogRequest(): void
    {
        $argument = new ArgumentMetadata('logRequest', LogRequest::class, false, false, null);
        $request = new Request();

        $this->assertTrue($this->resolver->supports($request, $argument));
    }

    public function testSupportsReturnsFalseForDifferentClass(): void
    {
        $argument = new ArgumentMetadata('someArg', \stdClass::class, false, false, null);
        $request = new Request();

        $this->assertFalse($this->resolver->supports($request, $argument));
    }

    public function testResolveReturnsValidLogRequest(): void
    {
        $request = new Request([
            'serviceNames' => ['auth'],
            'statusCode' => '200',
            'startDate' => '2024-01-01T00:00:00+00:00',
            'endDate' => '2024-01-31T23:59:59+00:00',
        ]);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $argument = new ArgumentMetadata('logRequest', LogRequest::class, false, false, null);
        $result = iterator_to_array($this->resolver->resolve($request, $argument));

        $this->assertCount(1, $result);
        $this->assertInstanceOf(LogRequest::class, $result[0]);
        $this->assertEquals(['auth'], $result[0]->serviceNames);
        $this->assertEquals(200, $result[0]->statusCode);
        $this->assertEquals('2024-01-01T00:00:00+00:00', $result[0]->startDate->format(\DateTimeInterface::ATOM));
        $this->assertEquals('2024-01-31T23:59:59+00:00', $result[0]->endDate->format(\DateTimeInterface::ATOM));
    }

    public function testResolveThrowsOnInvalidDate(): void
    {
        $request = new Request([
            'startDate' => 'invalid-date',
        ]);

        $argument = new ArgumentMetadata('logRequest', LogRequest::class, false, false, null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid date format.');

        iterator_to_array($this->resolver->resolve($request, $argument));
    }

    public function testResolveThrowsOnValidationFailure(): void
    {
        $request = new Request([
            'serviceNames' => ['auth'],
            'statusCode' => '500',
        ]);

        $violations = $this->createMock(ConstraintViolationList::class);
        $violations->method('count')->willReturn(1);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violations);

        $argument = new ArgumentMetadata('logRequest', LogRequest::class, false, false, null);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid log request parameters.');

        iterator_to_array($this->resolver->resolve($request, $argument));
    }
}
