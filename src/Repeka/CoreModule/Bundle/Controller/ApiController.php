<?php
namespace Repeka\CoreModule\Bundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ApiController extends Controller {
    protected function createJsonResponse($data, $status = 200): JsonResponse {
        $json = $this->container->get('jms_serializer')->serialize($data, 'json');
        $response = new JsonResponse();
        $response->setJson($json);
        $response->setStatusCode($status);
        return $response;
    }
}
