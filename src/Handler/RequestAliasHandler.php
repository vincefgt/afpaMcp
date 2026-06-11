<?php

declare(strict_types=1);

namespace App\Handler;

use Symfony\Component\HttpFoundation\Request;

class RequestAliasHandler
{

    /**
     * Default attribute that defines the alias list
     *
     * @var string
     */
    const ALIAS_FIELD = 'alias';

    /**
     * Execute the aliases of the attributes
     *
     * @param Request $request
     *
     * @return Request
     */
    public function handleAlias(Request $request): Request
    {
        if ($request->attributes->has(self::ALIAS_FIELD)) {
            foreach ($request->attributes->get(self::ALIAS_FIELD) as $alias => $new) {
                if ($request->attributes->has($alias)) {
                    $request->attributes->add([$new => $request->attributes->get($alias)]);
                }
            }
        }

        return $request;
    }
}
