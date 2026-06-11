<?php

declare(strict_types=1);

namespace App\Handler;

use Symfony\Component\HttpFoundation\Request;

class RequestMappingHandler
{
    /**
     * Default attribute that defines the mapping list
     *
     * @var string
     */
    const MAPPING_FIELD = 'mapping';

    /**
     * Execute the mapping of the attributes
     *
     * @param Request $request
     *
     * @return Request
     */
    public function handleMapping(Request $request): Request
    {
        if ($request->attributes->has(self::MAPPING_FIELD)) {
            foreach ($request->attributes->get(self::MAPPING_FIELD) as $attribute => $field) {
                if ($field != null) {
                    $request->request->add(
                        [
                            $attribute => json_encode([$field => $request->get($attribute)])
                        ]
                    );
                    $request->attributes->remove($attribute);
                    $request->query->remove($attribute);
                }
            }
        }

        foreach ($request->attributes->all() as $attribute => $value) {
            if (!is_array($value) && !$request->request->has($attribute)) {
                $request->request->add([$attribute => $value]);
            }
        }

        return $request;
    }
}
