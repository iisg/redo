<?php
namespace Repeka\Application\ParamConverter;

use Repeka\Domain\Repository\MetadataRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AssigneeMetadataParamConverter implements ParamConverterInterface {
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(MetadataRepository $metadataRepository) {
        $this->metadataRepository = $metadataRepository;
    }

    public function apply(Request $request, ParamConverter $configuration): bool {
        $paramName = $configuration->getName();
        if (!$request->query->has($paramName)) {
            $request->attributes->set($paramName, null);
        } else {
            $metadataId = intval($request->query->get($paramName));
            $metadata = $this->metadataRepository->findOne($metadataId);
            $request->attributes->set($paramName, $metadata);
        }
        return true;
    }

    /** @inheritdoc */
    public function supports(ParamConverter $configuration): bool {
        return true;
    }
}
