<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\XmlImport\XmlImportQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/xml-import")
 */
class XmlImportController extends ApiController {
    /**
     * @Route("/{id}")
     * @Method("POST")
     */
    public function getAction(string $id, Request $request) {
        $data = $request->request->all();
        Assertion::notEmpty($data['config'] ?? []);
        Assertion::notEmpty($data['resourceKind'] ?? []);
        $resourceKind = $this->handleCommand(new ResourceKindQuery($data['resourceKind']));
        Assertion::isJsonString($data['config'], 'Invalid config.');
        $config = json_decode($data['config'], true);
        Assertion::isArray($config, 'Invalid config.');
        $importedValues = $this->handleCommand(new XmlImportQuery($id, $config, $resourceKind));
        return $this->createJsonResponse($importedValues);
    }
}
