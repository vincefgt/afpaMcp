<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\Authentication;
use App\Entity\JsonApiError;
use App\Entity\JsonApiObject;
use App\Form\AuthenticationType;
use App\Formatter\ErrorFormatter;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class AuthenticationRequestValidator
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * @var ErrorFormatter
     */
    private $errorFormatter;

    /**
     * AuthenticationRequestValidator constructor.
     *
     * @param LoggerInterface      $logger
     * @param SerializerInterface  $serializer
     * @param FormFactoryInterface $factory
     * @param ErrorFormatter       $errorFormatter
     */
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        FormFactoryInterface $factory,
        ErrorFormatter $errorFormatter
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->factory = $factory;
        $this->errorFormatter = $errorFormatter;
    }

    /**
     *
     * Return an Authentication Object from the request
     *
     * @param Request $request
     *
     * @return Authentication
     */
    public function validateAuthenticationRequest(Request $request): Authentication
    {
        if (!$request->headers->get('api-domain')) {
            $this->logger->debug('Missing api-domain in header');
            throw new RuntimeException('Missing api-domain in header');
        }

        $form = $this->factory->create(AuthenticationType::class, new Authentication());
        $form->submit($request->request->all());
        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->debug('Form is submit and valid');

            return $form->getData();
        }

        $this->logger->debug('Form error detected', ['errorForm' => $this->errorFormatter->format($form)]);
        throw new RuntimeException('Invalid request form');
    }


    /**
     * Returns an array of errors for the form
     *
     * @param Request $request
     *
     * @return JsonApiObject|null
     */
    public function getErrorResponse(Request $request): ?JsonApiObject
    {
        $errors = new JsonApiObject();

        if (!$request->headers->get('api-domain')) {
            $errors->addError(new JsonApiError('Missing api-domain', 'api-domain in header is mandatory'));
        }

        $form = $this->factory->create(AuthenticationType::class, new Authentication());
        $form->submit($request->request->all());

        if ($form->isSubmitted() && !$form->isValid()) {
            $errors->addErrors($this->errorFormatter->format($form));
        }

        if (!empty($errors->errors)) {
            return $errors;
        }

        return null;
    }
}
