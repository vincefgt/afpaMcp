<?php

declare(strict_types=1);

namespace App\Client;

use App\Entity\ApiGatewayHeaders;
use App\Resolver\FileDirectoryResolver;
use Psr\Log\LoggerInterface;
use Symfony\Component\Intl\Exception\MissingResourceException;

class FileSystemClient
{
    /**
     * @var FileDirectoryResolver
     */
    private $directoryResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FileSystemClient constructor.
     *
     * @param FileDirectoryResolver $directoryResolver
     */
    public function __construct(FileDirectoryResolver $directoryResolver, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->directoryResolver = $directoryResolver;
    }

    public function downloadLink(string $fid, ApiGatewayHeaders $headers)
    {
        $basePath = $this->directoryResolver->resolveUrlDownloadByHeader($headers);
        $pathInfo = pathinfo($fid);
        $fileUrl = $basePath.$headers->getApiIdSession() . '/' . $pathInfo['basename'];
        $fileUrl = str_replace(" ", "%20", $fileUrl);

        return $fileUrl;
    }

    /**
     * @param string            $fid
     * @param ApiGatewayHeaders $headers
     *
     * @return false|string
     */
    public function download(string $fid, ApiGatewayHeaders $headers)
    {
        $basePath = $this->directoryResolver->resolveUrlByHeader($headers);
        $pathInfo = pathinfo($fid);
        $fileUrl = $basePath.$headers->getApiIdSession() . '/' . $pathInfo['basename'];
        $fileUrl = str_replace(" ", "%20", $fileUrl);

        try {
            $content = file_get_contents($fileUrl);
        } catch (\Exception $e) {
            $this->logger->warning(
                'File not found',
                [
                    'file' => $fid,
                    'session' => $headers->getApiIdSession()
                ]
            );

            throw new MissingResourceException('File not found');
        }

        return $content;
    }
}
