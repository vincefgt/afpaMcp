<?php
// src/Security/ApiKeyAuthenticator.php
namespace App\Security;

use App\Entity\ApiGatewayHeaders;
use App\Manager\JwtManager;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticator
{
    /**
     * @var JwtManager
     */
    private $jwtManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ApiGatewayHeaders
     */
    private $headers;

    /**
     * TokenAuthenticator constructor.
     *
     * @param JwtManager      $jwtManager
     * @param LoggerInterface $logger
     */
    public function __construct(JwtManager $jwtManager, LoggerInterface $logger)
    {
        $this->jwtManager = $jwtManager;
        $this->logger = $logger;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning false will cause this authenticator
     * to be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool
    {
        //@codingStandardsIgnoreEnd
        $apiToken = $this->getCredentials($request)['token'];


        try {
            if (empty($apiToken)) return false;
            $this->headers = $this->jwtManager->decodeToken($apiToken);
        } catch (RuntimeException $exception) {
            $this->logger->warning('Invalid token', ['exception' => $exception->getMessage()]);
            return false;
        }

        return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     *
     * @param Request $request
     *
     * @return array|mixed
     */
    public function getCredentials(Request $request): array
    {
        $token = trim(str_replace('Bearer', '', (string)$request->headers->get('Authorization')));

        return [
            'token' => $token
        ];
    }

    /**
     * Check the credentials for the user
     *
     * @param mixed         $credentials
     *
     * @return bool
     * @codingStandardsIgnoreStart
     * @SuppressWarnings("UnusedFormalParameter")
     */
    public function checkCredentials($credentials): bool
    {
        //@codingStandardsIgnoreEnd
        return $this->jwtManager->validateToken($credentials['token']);
    }
}
