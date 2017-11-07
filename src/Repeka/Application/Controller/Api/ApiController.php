<?php

namespace Repeka\Application\Controller\Api;

use Repeka\Application\Cqrs\CommandBusAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController extends Controller {
    use CommandBusAware;

    protected function createJsonResponse($data, $status = 200): JsonResponse {
        $json = $this->get('serializer')->serialize($data, 'json');
        return new JsonResponse($json, $status, [], true);
    }

    protected function createXmlResponse($xmlString, $status = 200): Response {
        $response = new Response($xmlString, $status);
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }
}
