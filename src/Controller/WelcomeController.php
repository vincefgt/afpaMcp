<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class WelcomeController extends AbstractController
{

    /**
     * @return JsonResponse
     */
    public function welcome()
    {
        return new JsonResponse(['data' => ['message' => 'welcome']]);
    }
}
