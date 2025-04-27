<?php

declare(strict_types=1);

namespace Interface\Http\Request;

use ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LogRequestResolver implements ArgumentValueResolverInterface
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function supports(Request $request, \ReflectionParameter $parameter): bool
    {
        return LogRequest::class === $parameter->getType()->getName();
    }

    public function resolve(Request $request, \ReflectionParameter $parameter): iterable
    { dd($request);
        $logRequest = new LogRequest();
        $logRequest->serviceNames = $request->query->get('serviceNames', []);
        $logRequest->statusCode = $request->query->get('statusCode');
        $logRequest->startDate = $request->query->get('startDate') ? new \DateTimeImmutable($request->query->get('startDate')) : null;
        $logRequest->endDate = $request->query->get('endDate') ? new \DateTimeImmutable($request->query->get('endDate')) : null;

        $errors = $this->validator->validate($logRequest);
        if (count($errors) > 0) {
            throw new BadRequestHttpException('Invalid log request parameters.');
        }

        yield $logRequest;
    }
}
