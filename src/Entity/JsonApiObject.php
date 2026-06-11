<?php

declare(strict_types=1);

namespace App\Entity;

use UnexpectedValueException;

class JsonApiObject implements \JsonSerializable
{
    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var object
     */
    public $data;

    /**
     * JsonApiObject constructor.
     *
     * @param JsonApiError|null $jsonApiError
     * @param null|mixed        $data
     */
    public function __construct(?JsonApiError $jsonApiError = null, $data = null)
    {
        if ($jsonApiError) {
            $this->addError($jsonApiError);
        }

        $this->setData($data);
    }

    /**
     * Add an error to the list of errors
     *
     * @param JsonApiError $error
     */
    public function addError(JsonApiError $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Add an error to the list of errors
     *
     * @param array|null $errors
     *
     * @return JsonApiObject
     */
    public function addErrors(?array $errors): self
    {
        if (!empty($errors)) {
            $this->errors = array_merge($this->errors, $errors);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     *
     * @return JsonApiObject
     */
    public function setErrors(array $errors): self
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     * @return null|object
     */
    public function getData(): object
    {
        return $this->data;
    }

    /**
     * @param null|object $data
     */
    public function setData(object $data = null): self
    {
        if ($data !== null
            && !\is_string($data)
            && !is_numeric($data)
            && !\is_callable([$data, '__toString'])
            && !\is_callable([$data, 'toString'])
        ) {
            throw new UnexpectedValueException(
                sprintf(
                    'The Response content must be a string or object implementing __toString(), "%s" given.',
                    \gettype($data)
                )
            );
        }

        $this->data = $data;

        return $this;
    }


    /**
     * Return the json string for the JsonErrors Object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }


    /**
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        if (!empty($this->errors)) {
            return ['errors' => $this->getErrors()];
        }

        return ['data' => (string)$this->getData()->toString()];
    }
}
