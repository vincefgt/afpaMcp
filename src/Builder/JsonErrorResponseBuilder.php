<?php

declare(strict_types=1);

namespace App\Builder;

use App\Entity\JsonApiError;
use App\Entity\JsonApiObject;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonErrorResponseBuilder
{

    /**
     * Create a Json Response when authentication controller is failing
     *
     * @param string $title
     * @param string $detail
     * @param int status
     *
     * @return JsonResponse
     */
    public function createErrorJsonResponse(string $title, string $detail, int $status): JsonResponse
    {
        return new JsonResponse(new JsonApiObject(new JsonApiError($title, $detail)), $status);
    }
}
