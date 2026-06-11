<?php

declare(strict_types=1);

namespace App\Controller;

use App\Builder\JsonErrorResponseBuilder;
use App\Manager\ApiGatewayClientManager;
use App\Manager\CacheManager;
use App\Manager\RequestConfigurationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    /**
     * @param Request                 $request
     * @param ApiGatewayClientManager $clientManager
     *
     * @return JsonResponse
     */
    public function query(
        Request $request,
        ApiGatewayClientManager $clientManager
    ): JsonResponse {
        return $clientManager->sendToApiGateway($request);
    }

    /**
     * @param Request                 $request
     * @param ApiGatewayClientManager $clientManager
     * @param CacheManager            $cacheManager
     *
     * @return JsonResponse
     */
    public function queryCache(
        Request $request,
        ApiGatewayClientManager $clientManager,
        CacheManager $cacheManager
    ): JsonResponse {
        if ($request->headers->hasCacheControlDirective('no-cache')) {
            return $this->query($request, $clientManager);
        }
        $cacheResponse = $cacheManager->getResponse($request);
        if ($cacheResponse) {
            return $cacheResponse;
        }
        $response = $clientManager->sendToApiGateway($request);
        if ($response->getStatusCode() == Response::HTTP_OK) {
            $cacheManager->saveResponse($request, $response);
        }

        return $response;
    }


    /**
     * @param Request                 $request
     * @param ApiGatewayClientManager $clientManager
     *
     * @return JsonResponse
     */
    public function create(
        Request $request,
        ApiGatewayClientManager $clientManager
    ): JsonResponse {
        return $clientManager->sendToApiGateway($request);
    }


    /**
     * @param Request                 $request
     * @param ApiGatewayClientManager $clientManager
     *
     * @return JsonResponse
     */
    public function show(
        Request $request,
        ApiGatewayClientManager $clientManager
    ): JsonResponse {
        return $clientManager->sendToApiGatewayWithId($request);
    }

    /**
     * @param Request                     $request
     * @param RequestConfigurationManager $configManager
     * @param ApiGatewayClientManager     $clientManager
     * @param JsonErrorResponseBuilder    $errorBuilder
     *
     * @return JsonResponse
     */
    public function update(
        Request $request,
        RequestConfigurationManager $configManager,
        ApiGatewayClientManager $clientManager,
        JsonErrorResponseBuilder $errorBuilder
    ): JsonResponse {
        if (!$configManager->checkPrimaryKeys($request)) {
            return $errorBuilder->createErrorJsonResponse(
                'Primary key error',
                'Bad primary key in request',
                Response::HTTP_BAD_GATEWAY
            );
        }

        return $clientManager->sendToApiGateway($request);
    }

    /**
     * @param Request                 $request
     * @param ApiGatewayClientManager $clientManager
     *
     * @return JsonResponse
     */
    public function delete(
        Request $request,
        ApiGatewayClientManager $clientManager
    ): JsonResponse {
        return $clientManager->sendToApiGatewayWithId($request);
    }
}
