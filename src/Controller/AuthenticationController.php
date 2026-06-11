<?php

declare(strict_types=1);

namespace App\Controller;

use App\Builder\ApiGatewayHeadersBuilder;
use App\Builder\JsonApiResponseBuilder;
use App\Manager\ApiGatewayClientManager;
use App\Manager\JwtManager;
use App\Validator\AuthenticationRequestValidator;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AuthenticationController
 *
 * @package App\Controller
 */
class AuthenticationController extends AbstractController
{

    /**
     * @param Request                        $request
     * @param AuthenticationRequestValidator $authValidator
     * @param JwtManager                     $jwtManager
     * @param ApiGatewayClientManager        $clientManager
     * @param ApiGatewayHeadersBuilder       $headersBuilder
     * @param JsonApiResponseBuilder         $responseBuilder
     *
     * @return JsonResponse
     */
    public function login(
        Request $request,
        AuthenticationRequestValidator $authValidator,
        JwtManager $jwtManager,
        ApiGatewayClientManager $clientManager,
        ApiGatewayHeadersBuilder $headersBuilder,
        JsonApiResponseBuilder $responseBuilder
    ): JsonResponse {
        try {
            $authValidator->validateAuthenticationRequest($request);
        } catch (RuntimeException $exception) {
            return new JsonResponse($authValidator->getErrorResponse($request), Response::HTTP_BAD_GATEWAY);
        }

        $apiResponse = $clientManager->sendToApiGateway($request);

        if ($apiResponse->getStatusCode() == Response::HTTP_OK) {
            $authHeader = $headersBuilder->createFromRequest($request, $jwtManager);
            $authHeader->setHeader(json_decode($apiResponse->getContent(), true)['data']);

            return $responseBuilder->createJsonApiResponse(
                $jwtManager->createToken($authHeader),
                $apiResponse->getStatusCode()
            );
        }

        return $apiResponse;
    }


    /**
     * @param Request                        $request
     * @param AuthenticationRequestValidator $authValidator
     * @param JwtManager                     $jwtManager
     * @param ApiGatewayClientManager        $clientManager
     * @param ApiGatewayHeadersBuilder       $headersBuilder
     * @param JsonApiResponseBuilder         $responseBuilder
     *
     * @return JsonResponse
     */
    public function changeLocal(
        Request $request,
        JwtManager $jwtManager,
        ApiGatewayClientManager $clientManager,
        ApiGatewayHeadersBuilder $headersBuilder,
        JsonApiResponseBuilder $responseBuilder
    ): JsonResponse {

        $apiResponse = $clientManager->sendToApiGateway($request);

        if ($apiResponse->getStatusCode() == Response::HTTP_OK) {
            $authHeader = $headersBuilder->createFromRequest($request, $jwtManager);
            $dataResponse = json_decode($apiResponse->getContent(), true);
            $local = isset($dataResponse['data']) ? (int)$dataResponse['data']['id_local'] : 0;
            $authHeader->setApiIdLocal($local);

            return $responseBuilder->createJsonApiResponse(
                $jwtManager->createToken($authHeader),
                $apiResponse->getStatusCode()
            );
        }

        return $apiResponse;
    }

    /**
     * @param Request                        $request
     * @param AuthenticationRequestValidator $authValidator
     * @param JwtManager                     $jwtManager
     * @param ApiGatewayClientManager        $clientManager
     * @param ApiGatewayHeadersBuilder       $headersBuilder
     * @param JsonApiResponseBuilder         $responseBuilder
     *
     * @return JsonResponse
     */
    public function commute(
        Request $request,
        JwtManager $jwtManager,
        ApiGatewayClientManager $clientManager,
        ApiGatewayHeadersBuilder $headersBuilder,
        JsonApiResponseBuilder $responseBuilder
    ): JsonResponse {

        $apiResponse = $clientManager->sendToApiGateway($request);

        if ($apiResponse->getStatusCode() == Response::HTTP_OK) {
            $authHeader = $headersBuilder->createFromRequest($request, $jwtManager);
            $authHeader->setHeader(json_decode($apiResponse->getContent(), true)['data']);

            return $responseBuilder->createJsonApiResponse(
                $jwtManager->createToken($authHeader),
                $apiResponse->getStatusCode()
            );
        }

        return $apiResponse;
    }

    /**
     * @param Request                        $request
     * @param AuthenticationRequestValidator $authValidator
     * @param JwtManager                     $jwtManager
     * @param ApiGatewayClientManager        $clientManager
     * @param ApiGatewayHeadersBuilder       $headersBuilder
     * @param JsonApiResponseBuilder         $responseBuilder
     *
     * @return JsonResponse
     */
    public function loginExtranet(
        Request $request,
        AuthenticationRequestValidator $authValidator,
        JwtManager $jwtManager,
        ApiGatewayClientManager $clientManager,
        ApiGatewayHeadersBuilder $headersBuilder
    ): JsonResponse {
        try {
            $authValidator->validateAuthenticationRequest($request);
        } catch (RuntimeException $exception) {
            return new JsonResponse($authValidator->getErrorResponse($request), Response::HTTP_BAD_GATEWAY);
        }

        $apiResponse = $clientManager->sendToApiGateway($request);

        if ($apiResponse->getStatusCode() == Response::HTTP_OK) {
            $authHeader = $headersBuilder->createFromRequest($request, $jwtManager);
            $result = json_decode($apiResponse->getContent(), true)['data'];
            $authHeader->setHeader($result);
            $token = $jwtManager->createToken($authHeader);
            $data = [
                'data' => sprintf(
                    '%s.%s.%s',
                    $token->headers()->toString(),
                    $token->claims()->toString(),
                    $token->signature()->toString()
                ),
                'user' => $result['extranetUser'],
                'gerant' => $result['gerant']
            ];

            return new JsonResponse(json_encode($data), $apiResponse->getStatusCode(), [], true);
        }

        return $apiResponse;
    }
}
