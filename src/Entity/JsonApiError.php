<?php

declare(strict_types=1);

namespace App\Entity;

class JsonApiError
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $detail;

    /**
     * JsonError constructor.
     *
     * @param string $title
     * @param string $detail
     */
    public function __construct(string $title, string $detail)
    {
        $this->title = $title;
        $this->detail = $detail;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return JsonApiError
     */
    public function setTitle(string $title): JsonApiError
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetail(): string
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     *
     * @return JsonApiError
     */
    public function setDetail(string $detail): JsonApiError
    {
        $this->detail = $detail;

        return $this;
    }
}
