<?php

declare(strict_types=1);

namespace App\Builder;

use App\Entity\ApiGatewayHeaders;
use App\Manager\JwtManager;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;

class ApiGatewayHeadersBuilder
{

    private JwtManager $jwtManager;
    private LoggerInterface $logger;

    public function __construct(JwtManager $jwtManager, LoggerInterface $logger)
    {
        $this->jwtManager = $jwtManager;
        $this->logger = $logger;
    }


    /**
     * Function that prepare the headers according to the request
     *
     * @param Request $request
     *
     * @return ApiGatewayHeaders
     */
    public function createFromRequest(Request $request, JwtManager $jwtManager = null,): ApiGatewayHeaders
    {

        $header = new ApiGatewayHeaders($request->attributes->all());
        $returnHeader = new ApiGatewayHeaders();

        $this->logger->info('Avant decodeToken' , ['header' => $request->headers]);

        if ($request->headers->has('Authorization')
            && trim(str_replace('Bearer', '', (string)$request->headers->get('Authorization'))) != 'undefined'
        ) {
            $apiToken = trim(str_replace('Bearer', '', (string)$request->headers->get('Authorization')));
            $returnHeader = $jwtManager->decodeToken($apiToken);
            self::setImportantHeadersInformations($returnHeader, $header);
            return $returnHeader;
        }

        $returnHeader->setApiDomain($request->headers->get('api-domain'));
        self::setImportantHeadersInformations($returnHeader, $header);

        return $returnHeader;
    }

    /**
     * Put the mandatory parameters on headers
     *
     * @param  ApiGatewayHeaders $apiGateway
     * @param  ApiGatewayHeaders $header
     * @return ApiGatewayHeaders
     */
    private function setImportantHeadersInformations(ApiGatewayHeaders $apiGateway, ApiGatewayHeaders $header)
    {
        $apiGateway->setApiFunction($header->getApiFunction());
        $apiGateway->setApiService($header->getApiService());
        $apiGateway->setApiMaxDepth($header->getApiMaxDepth());

        return $apiGateway;
    }
}
