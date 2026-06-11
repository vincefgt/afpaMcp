<?php

declare(strict_types=1);

namespace App\Builder;

use App\Entity\KeyCache;
use App\Manager\JwtManager;
use App\Manager\RequestConfigurationManager;
use Symfony\Component\HttpFoundation\Request;

class KeyCacheBuilder
{
    /**
     * @var ApiGatewayHeadersBuilder
     */
    private $headersBuilder;

    /**
     * @var RequestConfigurationManager
     */
    private $configManager;

    /**
     * @var JwtManager
     */
    private $jwtManager;

    /**
     * KeyCacheBuilder constructor.
     *
     * @param ApiGatewayHeadersBuilder    $headersBuilder
     * @param RequestConfigurationManager $configManager
     */
    public function __construct(
        ApiGatewayHeadersBuilder $headersBuilder,
        RequestConfigurationManager $configManager,
        JwtManager $jwtManager
    ) {
        $this->headersBuilder = $headersBuilder;
        $this->configManager = $configManager;
        $this->jwtManager = $jwtManager;
    }

    /**
     * Build the KeyCache from the request
     *
     * @param Request $request
     *
     * @return KeyCache
     */
    public function buildCacheKey(Request $request): KeyCache
    {
        $headers = $this->headersBuilder->createFromRequest($request, $this->jwtManager);

        return new KeyCache(
            $headers->getApiDomain(),
            $headers->getApiIdLocal(),
            $headers->getApiService(),
            $headers->getApiFunction(),
            $this->configManager->getParameters($request)
        );
    }
}
