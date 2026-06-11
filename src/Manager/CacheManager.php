<?php

declare(strict_types=1);

namespace App\Manager;

use App\Builder\KeyCacheBuilder;
use App\Entity\ApiGatewayHeaders;
use App\Serializer\KeyCacheSerializer;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CacheManager
{
    /**
     * @var KeyCacheBuilder
     */
    private $keyCacheBuilder;

    /**
     * @var KeyCacheSerializer
     */
    private $keyCacheSerializer;

    /**
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * @var int
     */
    private $timeout;

    /**
     * CacheManager constructor.
     *
     * @param FilesystemAdapter $filesystemAdapter
     * @param KeyCacheBuilder   $keyCacheBuilder
     * @param int               $timeout
     */
    public function __construct(
        FilesystemAdapter $filesystemAdapter,
        KeyCacheBuilder $keyCacheBuilder,
        KeyCacheSerializer $keyCacheSerializer,
        int $timeout
    ) {
        $this->cache = $filesystemAdapter;
        $this->keyCacheBuilder = $keyCacheBuilder;
        $this->keyCacheSerializer = $keyCacheSerializer;
        $this->timeout = $timeout;
    }

    /**
     * Save the response in cache
     *
     * @param Request      $request
     * @param JsonResponse $response
     */
    public function saveResponse(Request $request, JsonResponse $response)
    {
        $this->cache->save(
            $this->cache->getItem(
                $this->keyCacheSerializer->serialize($this->keyCacheBuilder->buildCacheKey($request))
            )
                ->expiresAfter(
                    $request->get(ApiGatewayHeaders::API_CACHE_DURATION) ? $request->get(
                        ApiGatewayHeaders::API_CACHE_DURATION
                    ) : $this->timeout
                )
                ->set($response->getContent())
        );
    }

    /**
     * Get the response in cache from the request
     *
     * @param Request $request
     *
     * @return JsonResponse|null
     */
    public function getResponse(Request $request)
    {
        $cacheItem = $this->cache->getItem(
            $this->keyCacheSerializer->serialize($this->keyCacheBuilder->buildCacheKey($request))
        );

        if ($cacheItem->isHit()) {
            return new JsonResponse($cacheItem->get(), Response::HTTP_OK, [], true);
        }

        return null;
    }
}
