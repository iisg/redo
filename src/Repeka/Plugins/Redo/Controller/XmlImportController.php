<?php
namespace Repeka\Plugins\Redo\Controller;

use Assert\Assertion;
use Repeka\Application\Controller\Api\ApiController;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\UseCase\MetadataImport\MarcxmlExtractQuery;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Repeka\Plugins\Redo\Service\KohaXmlResourceDownloader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api/xml-import")
 */
class XmlImportController extends ApiController {
    /** @var KohaXmlResourceDownloader */
    private $downloader;
    /** @var ImportConfigFactory */
    private $importConfigFactory;

    public function __construct(KohaXmlResourceDownloader $downloader, ImportConfigFactory $importConfigFactory) {
        $this->downloader = $downloader;
        $this->importConfigFactory = $importConfigFactory;
    }

    /**
     * @Route("/{id}")
     * @Method("POST")
     * @Security("has_role('ROLE_OPERATOR_SOME_CLASS')")
     */
    public function getAction(string $id, Request $request) {
        $data = $request->request->all();
        Assertion::notEmpty($data['config'] ?? []);
        Assertion::notEmpty($data['resourceKind'] ?? []);
        $resourceKind = $this->handleCommand(new ResourceKindQuery($data['resourceKind']));
        $resourceXml = $this->downloader->downloadById($id);
        if ($resourceXml === null) {
            throw new EntityNotFoundException('xmlResource', $id);
        }
        $extractedValues = $this->handleCommand(new MarcxmlExtractQuery($resourceXml, $id));
        $importConfig = $this->importConfigFactory->fromString($data['config'], $resourceKind);
        $importedValues = $this->handleCommand(new MetadataImportQuery($extractedValues, $importConfig));
        return $this->createJsonResponse($importedValues);
    }
}
