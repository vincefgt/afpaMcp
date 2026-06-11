<?php

declare(strict_types=1);

namespace App\Entity;

class ApiGatewayHeaders
{
    public const API_DOMAIN = 'api-domain';
    public const API_ID_USER = 'api-id-user';
    public const API_ID_USER_EXTRANET = 'api-id-user-extranet';
    public const API_ID_LOCAL = 'api-id-local';
    public const API_ID_SESSION = 'api-id-session';
    public const API_CHANGE_PASSWORD = 'api-change-password';
    public const API_SERVICE = 'api-service';
    public const API_FUNCTION = 'api-function';
    public const API_MAX_DEPTH = 'api-max-depth';
    public const API_CACHE_DURATION = 'api-cache-duration';

    /** Api Domain
     *
     * @var string
     */
    private $apiDomain;

    /** Api IdUser
     *
     * @var int
     */
    private $apiIdUser;

    /** Api IdUserExtranet
     *
     * @var int
     */
    private $apiIdUserExtranet;

    /** Api IdLocal
     *
     * @var int
     */
    private $apiIdLocal;

    /** Api IdSession
     *
     * @var string
     */
    private $apiIdSession;

    /** Api OldPassword
     * 
     * @var string
     */
    private $apiChangePassword;

    /** Api Service
     *
     * @var string
     */
    private $apiService;

    /** Api Function
     *
     * @var string
     */
    private $apiFunction;

    /**
     * Max depth
     *
     * @var int
     */
    private $apiMaxDepth;

    /**
     * cache duration
     *
     * @var int|null
     */
    private $apiCacheDuration;

    private $mapping = [
        self::API_DOMAIN => ['apiDomain', 'string'],
        self::API_ID_USER => ['apiIdUser', 'integer'],
        self::API_ID_USER_EXTRANET => ['apiIdUserExtranet', 'integer'],
        self::API_ID_LOCAL => ['apiIdLocal', 'integer'],
        self::API_CHANGE_PASSWORD => ['apiChangePassword', 'string'],
        self::API_ID_SESSION => ['apiIdSession', 'string'],
        self::API_SERVICE => ['apiService', 'string'],
        self::API_FUNCTION => ['apiFunction', 'string'],
        self::API_MAX_DEPTH => ['apiMaxDepth', 'int'],
        self::API_CACHE_DURATION => ['apiCacheDuration', 'int']
    ];

    /**
     * ApiGatewayHeaders constructor.
     *
     * @param array $headers
     */
    public function __construct($headers = [])
    {
        $this->setHeader($headers);
    }

    /**
     * @param array $headers
     *
     * @return ApiGatewayHeaders
     */
    public function setHeader($headers = []): ApiGatewayHeaders
    {
        foreach ($headers as $key => $value) {
            if (isset($this->mapping[$key])) {
                $fieldInfo = $this->mapping[$key];
                settype($value, $fieldInfo[1]);
                $this->{$fieldInfo[0]} = $value;
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiDomain(): ?string
    {
        return $this->apiDomain;
    }


    /**
     * @param string|null $apiDomain
     *
     * @return ApiGatewayHeaders
     */
    public function setApiDomain(?string $apiDomain)
    {
        $this->apiDomain = $apiDomain;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getApiIdUser(): ?int
    {
        return $this->apiIdUser;
    }

    /**
     * @param int|null $apiIdUser
     *
     * @return ApiGatewayHeaders
     */
    public function setApiIdUser(?int $apiIdUser): ApiGatewayHeaders
    {
        $this->apiIdUser = $apiIdUser;

        return $this;
    }
    
    /**
     * @return int|null
     */
    public function getApiIdUserExtranet(): ?int
    {
        return $this->apiIdUserExtranet;
    }

    /**
     * @param int|null $apiIdUserExtranet
     *
     * @return ApiGatewayHeaders
     */
    public function setApiIdUserExtranet(?int $apiIdUserExtranet): ApiGatewayHeaders
    {
        $this->apiIdUserExtranet = $apiIdUserExtranet;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getApiIdLocal(): ?int
    {
        return $this->apiIdLocal;
    }

    /**
     * @param int|null $apiIdLocal
     *
     * @return ApiGatewayHeaders
     */
    public function setApiIdLocal(?int $apiIdLocal): ApiGatewayHeaders
    {
        $this->apiIdLocal = $apiIdLocal;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getApiIdSession(): ?string
    {
        return $this->apiIdSession;
    }

    /**
     * @param string|null $apiIdSession
     *
     * @return ApiGatewayHeaders
     */
    public function setApiIdSession(?string $apiIdSession): ApiGatewayHeaders
    {
        $this->apiIdSession = $apiIdSession;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getApiChangePassword(): ?string
    {
        return $this->apiChangePassword;
    }

    /**
     * @param int|null $apiChangePassword
     *
     * @return ApiGatewayHeaders
     */
    public function setApiChangePassword(?string $apiChangePassword): ApiGatewayHeaders
    {
        $this->apiChangePassword = $apiChangePassword;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getApiService(): ?string
    {
        return $this->apiService;
    }

    /**
     * @param string|null $apiService
     *
     * @return ApiGatewayHeaders
     */
    public function setApiService(?string $apiService): ApiGatewayHeaders
    {
        $this->apiService = $apiService;

        return $this;
    }


    /**
     * @return string|null
     */
    public function getApiFunction(): ?string
    {
        return $this->apiFunction;
    }

    /**
     * @param string|null $apiFunction
     *
     * @return ApiGatewayHeaders
     */
    public function setApiFunction(?string $apiFunction): self
    {
        $this->apiFunction = $apiFunction;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getApiMaxDepth(): ?int
    {
        return $this->apiMaxDepth;
    }

    /**
     * @param int|null $apiMaxDepth
     */
    public function setApiMaxDepth(?int $apiMaxDepth): self
    {
        $this->apiMaxDepth = $apiMaxDepth;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getApiCacheDuration(): ?int
    {
        return $this->apiCacheDuration;
    }

    /**
     * @param int|null $apiCacheDuration
     *
     * @return $this
     */
    public function setApiCacheDuration(?int $apiCacheDuration): self
    {
        $this->apiCacheDuration = $apiCacheDuration;

        return $this;
    }

    /**
     * Transform the object headers into an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $headers = [];
        foreach ($this->mapping as $header => $fieldInfo) {
            $headers[$header] = $this->{$fieldInfo[0]};
        }

        return $headers;
    }
}
