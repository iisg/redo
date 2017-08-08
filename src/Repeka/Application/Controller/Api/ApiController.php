<?php

namespace Repeka\Application\Controller\Api;

use Repeka\Application\Cqrs\CommandBusAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ApiController extends Controller {
    use CommandBusAware;

    protected function createJsonResponse($data, $status = 200): JsonResponse {
        $json = $this->get('serializer')->serialize($data, 'json');
        $response = new JsonResponse($json, $status, [], true);
        return $response;
    }
}
