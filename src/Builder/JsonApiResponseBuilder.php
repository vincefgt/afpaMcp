<?php

declare(strict_types=1);

namespace App\Builder;

use App\Entity\JsonApiObject;
use Symfony\Component\HttpFoundation\JsonResponse;

class JsonApiResponseBuilder
{

    /**
     * Build a JsonApiResponse from data
     *
     * @param object $data
     * @param int    $status
     *
     * @return JsonResponse
     */
    public function createJsonApiResponse(object $data, int $status): JsonResponse
    {
        return new JsonResponse(new JsonApiObject(null, $data), $status);
    }
}
