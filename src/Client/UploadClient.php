<?php

declare(strict_types=1);

namespace App\Client;

use App\Builder\ApiGatewayOptionsBuilder;
use App\Entity\ApiGatewayHeaders;
use App\Resolver\ApiGatewayUrlResolver;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class UploadClient
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var ApiGatewayOptionsBuilder
     */
    private $optionsBuilder;

    /**
     * @var ApiGatewayUrlResolver
     */
    private $urlResolver;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ApiGatewayClient constructor.
     *
     * @param ApiGatewayUrlResolver    $urlResolver
     * @param ApiGatewayOptionsBuilder $optionsBuilder
     * @param LoggerInterface          $logger
     * @param Client                   $client
     */
    public function __construct(
        ApiGatewayUrlResolver $urlResolver,
        ApiGatewayOptionsBuilder $optionsBuilder,
        LoggerInterface $logger,
        Client $client
    ) {
        $this->optionsBuilder = $optionsBuilder;
        $this->urlResolver = $urlResolver;
        $this->logger = $logger;
        $this->httpClient = $client;
    }

    /**
     * Send the request via httpClient
     *
     * @param ApiGatewayHeaders $headers
     * @param array|null        $data
     *
     * @return ResponseInterface
     */
    public function call(ApiGatewayHeaders $headers, ?array $data = null): ResponseInterface
    {
        return $this->httpClient->request(
            Request::METHOD_POST,
            $this->urlResolver->resolveUploadUrlByHeader($headers),
            $this->optionsBuilder->buildMultiPartOptions($headers, $data)
        );
    }
}
