<?php

declare(strict_types=1);

namespace Interface\Http\Request;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LogRequestResolver implements ValueResolverInterface
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return LogRequest::class === $argument->getType();
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $logRequest = new LogRequest();

        $serviceNames = $request->query->all('serviceNames');
        $logRequest->serviceNames = $serviceNames;

        $logRequest->statusCode = $request->query->getInt('statusCode') ?: null;

        try {
            $logRequest->startDate = $request->query->get('startDate')
                ? new \DateTimeImmutable($request->query->get('startDate'))
                : null;

            $logRequest->endDate = $request->query->get('endDate')
                ? new \DateTimeImmutable($request->query->get('endDate'))
                : null;
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid date format.');
        }

        $errors = $this->validator->validate($logRequest);
        if (count($errors) > 0) {
            throw new BadRequestHttpException('Invalid log request parameters.');
        }

        yield $logRequest;
    }
}
