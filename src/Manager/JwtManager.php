<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ApiGatewayHeaders;
use App\Validator\ApiGatewayHeadersValidator;
use DateTimeImmutable;
use Exception;
use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\IssuedBy;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

/**
 * Class JwtManager
 *
 * @package App\Manager\JwtManager
 */
class JwtManager implements JwtManagerInterface
{
    /**
     * @var ApiGatewayHeadersValidator
     */
    private $headersValidator;

    /**
     * @var string
     */
    private $issuer;

    /**
     * @var string
     */
    private $key;

    /**
     * @var Configuration
     */
    private Configuration $config; 
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * JwtManager constructor.
     *
     * @param ApiGatewayHeadersValidator $headersValidator
     * @param string                     $issuer
     */
    public function __construct(
        ApiGatewayHeadersValidator $headersValidator,
        string $issuer,
        string $key,
        LoggerInterface $logger
    ) {
        $this->headersValidator = $headersValidator;
        $this->issuer = $issuer;
        self::setKey($key);

        $this->config = Configuration::forSymmetricSigner(new Sha256(), $this->key);
        $this->logger = $logger;
    }

    /**
     * @param ApiGatewayHeaders $apiGatewayHeaders
     *
     * @return Token
     * @throws  RuntimeException
     */
    public function createToken(ApiGatewayHeaders $apiGatewayHeaders): Token
    {
        $now   = new DateTimeImmutable();

        if ($this->headersValidator->validateApiGatewayHeaders($apiGatewayHeaders)) {
          $issuedAt = $now->modify('-2 hours 30 minutes');
        
        // Date limite : 00:00:01 du jour actuel
        $minIssuedAt = $now->setTime(0, 0, 1);
        
        // Prendre la plus récente des deux dates
        if ($issuedAt < $minIssuedAt) {
            $issuedAt = $minIssuedAt;
        }    
        return ($this->config->builder()->issuedBy($this->issuer)
                ->withClaim(ApiGatewayHeaders::API_DOMAIN, $apiGatewayHeaders->getApiDomain())
                ->withClaim(ApiGatewayHeaders::API_ID_USER, $apiGatewayHeaders->getApiIdUser())
                ->withClaim(ApiGatewayHeaders::API_ID_USER_EXTRANET, $apiGatewayHeaders->getApiIdUserExtranet())
                ->withClaim(ApiGatewayHeaders::API_ID_LOCAL, $apiGatewayHeaders->getApiIdLocal())
                ->withClaim(ApiGatewayHeaders::API_ID_SESSION, $apiGatewayHeaders->getApiIdSession())
                ->withClaim(ApiGatewayHeaders::API_CHANGE_PASSWORD, $apiGatewayHeaders->getApiChangePassword())
                ->issuedAt($issuedAt)
                ->expiresAt($now->modify('tomorrow')->modify('-1 second'))
                ->getToken($this->config->signer(), $this->config->signingKey())
            );
        }

        throw new RuntimeException('Error creating token');
    }

    /**
     * Transform token into ApiGatewayHeaders
     *
     * @param string $jwtToken
     *
     * @return ApiGatewayHeaders
     * @throws RuntimeException
     */
    public function decodeToken(string $jwtToken): ApiGatewayHeaders
    {
        $apiGatewayHeaders = null;
        try {
            $token = $this->config->parser()->parse($jwtToken);
        } catch (Exception $exception) {
            $this->logger->warning('The token could not be parsed', ['exception' => $exception->getMessage(), 'pile' => $exception->getTraceAsString()]);
            throw new RuntimeException('The token could not be parsed', Response::HTTP_UNAUTHORIZED);
        }

        if ($this->validateToken($token->toString())) {
            $mapping = $token->claims()->all();
            $this->logger->debug('$this->validateToken', ['token' => $token, 'string' => $token->toString()]);
            $apiGatewayHeaders = new ApiGatewayHeaders($mapping);
        }

        if (is_null($apiGatewayHeaders)) {
            // $this->logger->warning('apiGatewayHeaders is null', ['exception' => 'erreur lors de la validation du token']);
            throw new RuntimeException('Le token n\'est pas valide', Response::HTTP_UNAUTHORIZED);
        } else {
            return $apiGatewayHeaders;
        }
    }

    /**
     * @param String $jwtToken
     *
     * @return bool
     */
    public function validateToken(string $jwtToken): bool
    {
        try {
            $this->logger->debug('validateToken', ['jwtToken' => $jwtToken]);
            $token = $this->config->parser()->parse($jwtToken);
            $contraints = [];
            $contraints[] = new IssuedBy($this->issuer);
            $contraints[] = new SignedWith($this->config->signer(), $this->config->signingKey());
            $dateTimeImmutable = new DateTimeImmutable();
            $newFrozenClock = new FrozenClock($dateTimeImmutable);
            $contraints[] = new LooseValidAt($newFrozenClock);
            $validationResult = $this->config->validator()->validate($token, ...$contraints);
            $this->logger->debug('validateToken suite', ['token' => $token,
                                            'contraints' => $contraints,
                                            'issuer' => $this->issuer,
                                            'clockDate' => $newFrozenClock->now()->format('Y-m-d H:i:s'),
                                            'signer' => $this->config->signer(),
                                            'signingKey' => substr($this->config->signingKey()->contents(), 0, 5) . '...' . substr($this->config->signingKey()->contents(), -5),
                                            'validationResult' => $validationResult
                                            ]); 
            return $validationResult;
        } catch (Exception $exception) {
            $this->logger->warning('validateToken exception', ['exception' => $exception->getMessage(), 'pile' => $exception->getTraceAsString()]);
            return false;
        }
    }
    
    /**
     * setKey
     *
     * @param  string $key
     * @return void
     */
    private function setKey(string $key)
    {
        $this->key = InMemory::file($key);
    }
}
