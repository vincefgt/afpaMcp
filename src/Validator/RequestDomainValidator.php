<?php

declare(strict_types=1);

namespace App\Validator;

use App\Entity\ApiGatewayHeaders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class RequestDomainValidator
{
    /**
     * @var Security
     */
    private $security;

    /**
     * Default attribute that defines the mapping list
     *
     * @var string
     */
    const DOMAIN_FIELD = 'allowed-domains';

    /**
     * RequestDomainHandler constructor.
     *
     * @param Security $security
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Execute the mapping of the attributes
     *
     * @param Request $request
     *
     * @return bool
     */
    public function validate(Request $request): bool
    {
        if ($request->headers->get(self::DOMAIN_FIELD)) {
            if (!in_array(
                $this->security->getUser()->getRoles()[ApiGatewayHeaders::API_DOMAIN],
                $request->headers->get(self::DOMAIN_FIELD)
            )) {
                return false;
            }
        }
        
        return true;
    }
}
