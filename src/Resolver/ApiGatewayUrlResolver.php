<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Entity\ApiGatewayHeaders;
use App\Mapper\DomainMapper;
use Psr\Log\LoggerInterface;
use RuntimeException;

class ApiGatewayUrlResolver
{

    /**
     * @var DomainMapper
     */
    private $domainMapper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ApiGatewayUrlResolver constructor.
     *
     * @param DomainMapper    $domainMapper
     * @param LoggerInterface $logger
     */
    public function __construct(DomainMapper $domainMapper, LoggerInterface $logger)
    {
        $this->domainMapper = $domainMapper;
        $this->logger = $logger;
    }

    /**
     * Resolve an url for a domain according to the configuration
     *
     * @param ApiGatewayHeaders $headers
     *
     * @return string
     */
    public function resolveUrlByHeader(ApiGatewayHeaders $headers): string
    {
        if (!empty($headers)) {
            $apiDomain = $headers->getApiDomain();
        }

        if (!empty($apiDomain)) {
            return $this->domainMapper->getUrlByDomain($apiDomain);
        }

        $this->logger->error('No domain found in header');
        throw new RuntimeException('No domain found in header');
    }

    /**
     * Resolve an upload url for a domain according to the configuration
     *
     * @param ApiGatewayHeaders $headers
     *
     * @return string
     */
    public function resolveUploadUrlByHeader(ApiGatewayHeaders $headers): string
    {
        if (!empty($headers)) {
            $apiDomain = $headers->getApiDomain();
        }

        if (!empty($apiDomain)) {
            return $this->domainMapper->getUploadByDomain($apiDomain);
        }

        $this->logger->error('No domain found in header');
        throw new RuntimeException('No domain found in header');
    }
}
