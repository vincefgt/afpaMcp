<?php

declare(strict_types=1);

namespace App\Entity;

class KeyCache
{
    /** Api Domain
     *
     * @var string
     */
    private $apiDomain;

    /** Api IdLocal
     *
     * @var int
     */
    private $apiIdLocal;

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

    /** Parameters
     *
     * @var array
     */
    private $parameters;

    /**
     * KeyCache constructor.
     *
     * @param string|null $apiDomain
     * @param string|null $apiIdLocal
     * @param string|null $apiService
     * @param string|null $apiFunction
     * @param array|null  $parameters
     */
    public function __construct(
        ?string $apiDomain,
        ?int $apiIdLocal,
        ?string $apiService,
        ?string $apiFunction,
        ?array $parameters
    ) {
        $this->apiDomain = $apiDomain;
        $this->apiIdLocal = $apiIdLocal;
        $this->apiService = $apiService;
        $this->apiFunction = $apiFunction;
        $this->parameters = $parameters;
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
     * @return KeyCache
     */
    public function setApiDomain(?string $apiDomain): self
    {
        $this->apiDomain = $apiDomain;

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
     * @return KeyCache
     */
    public function setApiIdLocal(?int $apiIdLocal): self
    {
        $this->apiIdLocal = $apiIdLocal;

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
     * @return KeyCache
     */
    public function setApiService(?string $apiService): self
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
     * @return KeyCache
     */
    public function setApiFunction(?string $apiFunction): self
    {
        $this->apiFunction = $apiFunction;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getParameters(): ?array
    {
        return $this->parameters;
    }

    /**
     * @param array|null $parameters
     *
     * @return KeyCache
     */
    public function setParameters(?array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }
}
