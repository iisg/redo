<?php
namespace Repeka\Application\ParamConverter;

use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ResourceContentsParamConverter implements ParamConverterInterface {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(MetadataRepository $metadataRepository, ResourceRepository $resourceRepository) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function apply(Request $request, ParamConverter $configuration): bool {
        try {
            $contents = $this->getContentsFromRequest($request);
        } catch (EntityNotFoundException $e) {
            $e->setCode(400);
            throw $e;
        }
        $request->attributes->set($configuration->getName(), $contents);
        return true;
    }

    /** @inheritdoc */
    public function supports(ParamConverter $configuration): bool {
        return true;
    }

    private function getContentsFromRequest(Request $request):array {
        $contents = [];
        foreach ($request->request->all()['contents'] ?? [] as $metadataId => $value) {
            $baseMetadata = $this->metadataRepository->findOne($metadataId);
            if ($baseMetadata->getControl() === 'relationship') {
                $relatedResource = $this->resourceRepository->findOne($value);
                $contents[$metadataId] = $relatedResource;
            } else {
                $contents[$metadataId] = $value;
            }
        }
        return $contents;
    }
}
