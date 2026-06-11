<?php

declare(strict_types=1);

namespace App\Manager;

use App\Handler\RequestAggregationHandler;
use App\Handler\RequestAliasHandler;
use App\Handler\RequestKeysHandler;
use App\Handler\RequestMappingHandler;
use App\Handler\RequestParametersHandler;
use Symfony\Component\HttpFoundation\Request;

class RequestConfigurationManager
{

    /**
     * @var RequestAliasHandler
     */
    private $aliasHandler;

    /**
     * @var RequestMappingHandler
     */
    private $mappingHandler;

    /**
     * @var RequestParametersHandler
     */
    private $parametersHandler;

    /**
     * @var RequestKeysHandler
     */
    private $keysHandler;

    /**
     * @var RequestAggregationHandler
     */
    private $aggregationHandler;

    /**
     * RequestConfigurationManager constructor.
     *
     * @param RequestAliasHandler       $aliasHandler
     * @param RequestMappingHandler     $mappingHandler
     * @param RequestParametersHandler  $parametersHandler
     * @param RequestKeysHandler        $requestKeysHandler
     * @param RequestAggregationHandler $aggregationHandler
     */
    public function __construct(
        RequestAliasHandler $aliasHandler,
        RequestMappingHandler $mappingHandler,
        RequestParametersHandler $parametersHandler,
        RequestKeysHandler $requestKeysHandler,
        RequestAggregationHandler $aggregationHandler
    ) {
        $this->aliasHandler = $aliasHandler;
        $this->mappingHandler = $mappingHandler;
        $this->parametersHandler = $parametersHandler;
        $this->keysHandler = $requestKeysHandler;
        $this->aggregationHandler = $aggregationHandler;
    }

    /**
     * Format the request with aliases, mapping and parameters
     *
     * @param Request $request
     *
     * @return array
     */
    public function getParameters(Request $request): Array
    {
        return $this->parametersHandler->handleParameters(
            $this->aggregationHandler->handleAggregation(
                $this->mappingHandler->handleMapping(
                    $this->aliasHandler->handleAlias(
                        $request
                    )
                )
            )
        )->request->all();
    }

    /**
     * Check the keys in parameters
     *
     * @param Request $request
     *
     * @return bool
     */
    public function checkPrimaryKeys(Request $request): bool
    {
        return $this->keysHandler->checkPrimaryKey($request);
    }
}
