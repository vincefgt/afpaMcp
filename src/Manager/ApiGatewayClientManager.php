<?php

declare(strict_types=1);

namespace App\Manager;

use App\Builder\ApiGatewayHeadersBuilder;
use App\Builder\JsonErrorResponseBuilder;
use App\Client\ApiGatewayClient;
use App\Handler\RequestParametersHandler;
use App\Validator\RequestDomainValidator;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiGatewayClientManager
{

    /**
     * @var JsonErrorResponseBuilder
     */
    private $errorBuilder;

    /**
     * @var ApiGatewayHeadersBuilder
     */
    private $headersBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ApiGatewayClient
     */
    private $apiClient;

    /**
     * @var RequestConfigurationManager
     */
    private $configManager;

    /**
     * @var RequestDomainValidator
     */
    private $domainValidator;

    /**
     * @var JwtManager
     */
    private $jwtManager;

    /**
     * ApiGatewayClientManager constructor.
     *
     * @param JsonErrorResponseBuilder    $errorBuilder
     * @param ApiGatewayHeadersBuilder    $headersBuilder
     * @param LoggerInterface             $logger
     * @param ApiGatewayClient            $apiClient
     * @param RequestConfigurationManager $configManager
     * @param RequestDomainValidator      $domainValidator
     */
    public function __construct(
        JsonErrorResponseBuilder $errorBuilder,
        ApiGatewayHeadersBuilder $headersBuilder,
        LoggerInterface $logger,
        ApiGatewayClient $apiClient,
        RequestConfigurationManager $configManager,
        RequestDomainValidator $domainValidator,
        JwtManager $jwtManager
    ) {
        $this->errorBuilder = $errorBuilder;
        $this->logger = $logger;
        $this->headersBuilder = $headersBuilder;
        $this->apiClient = $apiClient;
        $this->configManager = $configManager;
        $this->domainValidator = $domainValidator;
        $this->jwtManager = $jwtManager;
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendToApiGateway(
        Request $request
    ): JsonResponse {
        
        // $this->logger->error('Test info with person data');
        if (!$this->domainValidator->validate($request)) {
            return $this->errorBuilder->createErrorJsonResponse(
                'Unauthorized',
                'Access to this route is not allowed for your domain',
                Response::HTTP_FORBIDDEN
            );
        }
       
        try {
            $apiResponse = $this->apiClient->call(
                Request::METHOD_POST,
                $this->headersBuilder->createFromRequest($request, $this->jwtManager),
                $this->configManager->getParameters($request)
            );
        } catch (Exeception $exeception) {
            $this->logger->error('ERP call error', ['message' => $exeception->getMessage()]);

            return $this->errorBuilder->createErrorJsonResponse(
                'Bad Gateway',
                $exeception->getMessage(),
                Response::HTTP_BAD_GATEWAY
            );
        }
        
        $apiResponseBody = $apiResponse->getBody()->getContents();
        $apiResponseArray = (!empty($apiResponseBody)) ? json_decode($apiResponseBody, true) : null;

        if (!$apiResponseArray) {
            $this->logger->error('ERP call syntax error', ['message' => $apiResponseBody]);

            return $this->errorBuilder->createErrorJsonResponse(
                'Bad server response',
                'Invalid response',
                Response::HTTP_BAD_GATEWAY
            );
        }

        if (!array_key_exists('data' , $apiResponseArray)){ 
            $sendError = true;
            if (isset($apiResponseArray['errors']) && isset($apiResponseArray['errors'][0])){
                if (isset($apiResponseArray['errors'][0]['title']) && $apiResponseArray['errors'][0]['title'] == 'Session not found' ){
                    $sendError = false; 
                }
            }
            if ($sendError) {
                $this->logger->warning('ApiGateway error response', ['message' => $apiResponseBody]);
            }
            return new JsonResponse($apiResponseBody, $apiResponse->getStatusCode(), [], true);
        }

        // $this->logger->debug('Successful response', ['message' => $apiResponseBody]);

        return new JsonResponse($apiResponseArray, $apiResponse->getStatusCode());
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function sendToApiGatewayWithId(
        Request $request
    ): JsonResponse {

        if (!$request->attributes->has('id')) {
            $this->logger->error('Primary key is missing', ['request' => $request]);

            return $this->errorBuilder->createErrorJsonResponse(
                'Primary key error',
                'Primary key is missing in request',
                Response::HTTP_BAD_GATEWAY
            );
        }

        $request->attributes->add([RequestParametersHandler::PARAMETERS_FIELD => ['id']]);

        return $this->sendToApiGateway($request);
    }
}
