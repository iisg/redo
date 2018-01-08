<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Domain\UseCase\XmlImport\XmlImportQuery;
use Repeka\Domain\XmlImport\XmlResourceDownloader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/xml-import")
 */
class XmlImportController extends ApiController {
    /** @var XmlResourceDownloader */
    private $downloader;

    public function __construct(XmlResourceDownloader $downloader) {
        $this->downloader = $downloader;
    }

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
        $resourceXml = $this->downloader->downloadById($id);
        if ($resourceXml === null) {
            throw new EntityNotFoundException('xmlResource', $id);
        }
        $importedValues = $this->handleCommand(new XmlImportQuery($resourceXml, $config, $resourceKind));
        return $this->createJsonResponse($importedValues);
    }
}
