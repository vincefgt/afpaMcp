<?php

declare(strict_types=1);

namespace App\Formatter;

use App\Entity\JsonApiError;
use Symfony\Component\Form\FormInterface;

/**
 * Class ErrorFormatter
 *
 * @package App\Formatter
 */
class ErrorFormatter
{
    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function format(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = new JsonApiError(
                $this->getErrorPath($error->getOrigin()),
                $error->getMessage()
            );
        }

        return $errors;
    }

    /**
     * @param FormInterface $form
     *
     * @return string
     */
    public function getErrorPath(FormInterface $form): string
    {
        $path = $form->getName();

        if ($form->getParent()) {
            $path = sprintf('%s.%s', $this->getErrorPath($form->getParent()), $path);
        }

        return $path;
    }
}
