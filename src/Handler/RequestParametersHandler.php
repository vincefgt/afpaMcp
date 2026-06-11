<?php

declare(strict_types=1);

namespace App\Handler;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

class RequestParametersHandler
{
    /**
     * Default attribute that defines the parameters list
     *
     * @var string
     */
    const PARAMETERS_FIELD = 'parameters';

    /**
     * Order the attributes of the Request
     *
     * @param Request $request
     *
     * @return Request $request
     */
    public function handleParameters(Request $request): Request
    {

        $parameterBag = new ParameterBag();
        if ($request->attributes->has(self::PARAMETERS_FIELD)) {
            foreach ($request->attributes->get(self::PARAMETERS_FIELD) as $attribute) {
                $parameterBag->add([$attribute => $request->get($attribute, '')]);
            }
        }

        $request->request = $parameterBag;

        return $request;
    }
}
