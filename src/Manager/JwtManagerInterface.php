<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ApiGatewayHeaders;
use Lcobucci\JWT\Token;
use RuntimeException;

interface JwtManagerInterface
{
    /**
     * @param ApiGatewayHeaders $apiGatewayHeaders
     *
     * @return Token
     * @throws RuntimeException
     */
    public function createToken(ApiGatewayHeaders $apiGatewayHeaders): Token;

    /**
     * @param string $jwtToken
     *
     * @return ApiGatewayHeaders
     * @throws RuntimeException
     */
    public function decodeToken(string $jwtToken): ApiGatewayHeaders;

    /**
     * @param String $jwtToken
     *
     * @return bool
     */
    public function validateToken(string $jwtToken): bool;
}
