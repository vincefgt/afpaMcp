<?php

namespace App\EventListener;

use App\Builder\JsonErrorResponseBuilder;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

class ExceptionListener implements EventSubscriberInterface
{
    private JsonErrorResponseBuilder $errorResponseBuilder;
     /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(JsonErrorResponseBuilder $errorResponseBuilder,LoggerInterface $logger)
    {
        $this->errorResponseBuilder = $errorResponseBuilder;
        $this->logger = $logger;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof RuntimeException && $exception->getCode() === Response::HTTP_UNAUTHORIZED) {
            $response = $this->errorResponseBuilder->createErrorJsonResponse(
                'Authentification échouée',
                $exception->getMessage(),
                Response::HTTP_UNAUTHORIZED
            );
            $this->logger->warning('Authentification échouée', ['exception' => $exception->getMessage(), 'pile' => $exception->getTraceAsString()]);
            $event->setResponse($response);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }
}
