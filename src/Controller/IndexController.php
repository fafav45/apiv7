<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class IndexController extends AbstractController
{
    public function __construct(
        LoggerInterface $logger
    ){
        $logger->info("IndexController construct");
    }

    public function index(): JsonResponse
    {
        return new JsonResponse([
            'message' => 'forbidden!'
        ], Response::HTTP_FORBIDDEN);
    }
}