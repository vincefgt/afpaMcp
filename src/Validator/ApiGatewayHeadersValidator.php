<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\ApiGatewayHeaders;
use RuntimeException;

class ApiGatewayHeadersValidator
{
    /**
     * Check the header for missing information
     *
     * @param ApiGatewayHeaders $apiGatewayHeaders
     *
     * @return bool
     */
    public function validateApiGatewayHeaders(ApiGatewayHeaders $apiGatewayHeaders): bool
    {
        if (empty($apiGatewayHeaders->getApiDomain())) {
            $this->throwMissingInfoException(ApiGatewayHeaders::API_DOMAIN);
        }

        if (empty($apiGatewayHeaders->getApiIdUser())) {
            $this->throwMissingInfoException(ApiGatewayHeaders::API_ID_USER);
        }

        if (empty($apiGatewayHeaders->getApiIdSession())) {
            $this->throwMissingInfoException(ApiGatewayHeaders::API_ID_SESSION);
        }

        return true;
    }

    /**
     *
     * @param string $missingInfo
     */
    private function throwMissingInfoException(string $missingInfo): void
    {
        throw new RuntimeException(sprintf('Missing information in header : %s', $missingInfo));
    }
}
