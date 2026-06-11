<?php

declare(strict_types=1);

namespace App\Handler;

use Symfony\Component\HttpFoundation\Request;

class RequestAggregationHandler
{
    /**
     * Default attribute that defines the mapping list
     *
     * @var string
     */
    const AGGREGATION_FIELD = 'aggregation';


    /**
     * Execute the aggregation of the attributes
     *
     * @param Request $request
     *
     * @return Request
     */
    public function handleAggregation(Request $request): Request
    {
        if ($request->attributes->has(self::AGGREGATION_FIELD)) {
            $aggregateProperties = $request->attributes->get(self::AGGREGATION_FIELD);

            if ($aggregateProperties) {
                foreach ($aggregateProperties as $aggregationName => $parameters) {
                    $aggregation = [];
                    foreach ((array)$parameters as $attribute => $source) {
                        $source = $request->request->get($source);

                        if (!empty($source) && json_decode($source)) {
                            $aggregation[$attribute] = json_decode($source);
                            continue;
                        }

                        if (!empty($source)) {
                            $aggregation[$attribute] = $source;
                            continue;
                        }
                    }

                    if (!empty($aggregation)) {
                        $request->attributes->add(
                            [$aggregationName => json_encode($aggregation)]
                        );
                    }
                }
            }
        }

        return $request;
    }
}
