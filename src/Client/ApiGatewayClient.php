<?php

declare(strict_types=1);

namespace App\Client;

use App\Builder\ApiGatewayOptionsBuilder;
use App\Entity\ApiGatewayHeaders;
use App\Resolver\ApiGatewayUrlResolver;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class ApiGatewayClient
{
    /**
     * @var string
     */
    private $method = null;

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
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param ?string $method
     *
     * @return ApiGatewayClient
     */
    public function setMethod($method): ApiGatewayClient
    {
        if (!in_array(
            $method,
            [
                Request::METHOD_GET,
                Request::METHOD_POST,
                Request::METHOD_PUT,
                Request::METHOD_DELETE
            ]
        )) {
            $this->logger->error('Option unknown', [$method]);
            throw new RuntimeException('Option unknown ' . $method);
        }
        $this->method = $method;

        return $this;
    }

    /**
     * Send the request via httpClient
     *
     * @param string            $method
     * @param ApiGatewayHeaders $headers
     * @param array|null        $data
     *
     * @return ResponseInterface
     */
    public function call(string $method, ApiGatewayHeaders $headers, ?array $data = null): ResponseInterface
    {
        $this->setMethod($method);

        $url = $this->urlResolver->resolveUrlByHeader($headers);
        $this->logger->debug(sprintf('Target url : %s', $url));

        return $this->httpClient->request(
            $this->method,
            $url,
            $this->optionsBuilder->buildOptions($headers, $data)
        );
    }
}
