<?php

declare(strict_types=1);

namespace App\Resolver;

use App\Entity\ApiGatewayHeaders;
use App\Mapper\FileDirectoryMapper;
use Psr\Log\LoggerInterface;
use RuntimeException;

class FileDirectoryResolver
{

    /**
     * @var FileDirectoryMapper
     */
    private $fileDirectoryMapper;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ApiGatewayUrlResolver constructor.
     *
     * @param FileDirectoryMapper $fileDirectoryMapper
     * @param LoggerInterface     $logger
     */
    public function __construct(FileDirectoryMapper $fileDirectoryMapper, LoggerInterface $logger)
    {
        $this->fileDirectoryMapper = $fileDirectoryMapper;
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
            return $this->fileDirectoryMapper->getUploadDirectoryByDomain($apiDomain);
        }

        $this->logger->error('No domain found in header');
        throw new RuntimeException('No domain found in header');
    }


    
    /**
     * Resolve an url for a domain according to the configuration
     *
     * @param ApiGatewayHeaders $headers
     *
     * @return string
     */
    public function resolveUrlDownloadByHeader(ApiGatewayHeaders $headers): string
    {
        if (!empty($headers)) {
            $apiDomain = $headers->getApiDomain();
        }

        if (!empty($apiDomain)) {
            return $this->fileDirectoryMapper->getDownloadDirectoryByDomain($apiDomain);
        }

        $this->logger->error('No domain found in header');
        throw new RuntimeException('No domain found in header');
    }


}
