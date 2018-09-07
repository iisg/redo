<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Application\MetadataImport\KohaXmlResourceDownloader;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Metadata\MetadataImport\Config\ImportConfigFactory;
use Repeka\Domain\UseCase\MetadataImport\MetadataImportQuery;
use Repeka\Domain\UseCase\MetadataImport\XmlExtractQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/xml-import")
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
     */
    public function getAction(string $id, Request $request) {
        $data = $request->request->all();
        Assertion::notEmpty($data['config'] ?? []);
        Assertion::notEmpty($data['resourceKind'] ?? []);
        $resourceKind = $this->handleCommand(new ResourceKindQuery($data['resourceKind']));
        Assertion::isJsonString($data['config'], 'Invalid config.');
        $config = json_decode($data['config'], true);
        Assertion::isArray($config, 'Invalid config.');
        Assertion::keyExists($config, 'xmlMappings', 'Missing xmlMappings key in config.');
        $resourceXml = $this->downloader->downloadById($id);
        if ($resourceXml === null) {
            throw new EntityNotFoundException('xmlResource', $id);
        }
        $extractedValues = $this->handleCommand(new XmlExtractQuery($resourceXml, $config['xmlMappings']));
        $importConfig = $this->importConfigFactory->fromArray($config, $resourceKind);
        $importedValues = $this->handleCommand(new MetadataImportQuery($extractedValues, $importConfig));
        return $this->createJsonResponse($importedValues);
    }
}
