<?php
namespace Repeka\Application\ParamConverter;

use Repeka\Application\ParamConverter\MetadataValueProcessor\MetadataValueProcessor;
use Repeka\Application\Upload\UploadSizeHelper;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\MetadataRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class ResourceContentsParamConverter implements ParamConverterInterface {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var MetadataValueProcessor */
    private $metadataValueProcessor;

    public function __construct(MetadataRepository $metadataRepository, MetadataValueProcessor $metadataValueProcessor) {
        $this->metadataRepository = $metadataRepository;
        $this->metadataValueProcessor = $metadataValueProcessor;
    }

    public function apply(Request $request, ParamConverter $configuration): bool {
        $contents = $this->getContentsFromRequest($request);
        $request->attributes->set($configuration->getName(), $contents);
        return true;
    }

    public function supports(ParamConverter $configuration): bool {
        return $configuration->getClass() == ResourceContents::class;
    }

    private function getContentsFromRequest(Request $request): ResourceContents {
        $contentsFromRequest = $request->get('contents');
        if (!$contentsFromRequest) {
            $this->handleEmptyRequestContents($request);
        }
        $decodedContents = json_decode($contentsFromRequest, true);
        $contents = ResourceContents::fromArray($decodedContents ?? []);
        return $this->processMetadataValues($contents, $request);
    }

    public function processMetadataValues(ResourceContents $contents, Request $request): ResourceContents {
        return $contents->mapAllValues(
            function (MetadataValue $value, int $metadataId) use ($request) {
                $metadata = $this->metadataRepository->findOne($metadataId);
                return $value->withNewValue($this->metadataValueProcessor->process($value->getValue(), $metadata->getControl(), $request));
            }
        );
    }

    /**
     * Tries to detect if the max upload size has been reached and throw appropriate exception then.
     *
     * @see http://stackoverflow.com/a/2133726/878514 how to detect upload limit reached?
     * @see http://stackoverflow.com/a/4445721/878514 how to get the max upload limit for current request?
     * @see http://ca2.php.net/manual/en/ini.core.php#66801 idea of comparison to $_SERVER['CONTENT_LENGTH']
     */
    private function handleEmptyRequestContents(Request $request) {
        $maxFileUploadSizeInBytes = (new UploadSizeHelper())->getMaxUploadSize();
        $actualUploadSize = $request->server->get('CONTENT_LENGTH');
        if ($actualUploadSize > $maxFileUploadSizeInBytes) {
            throw new DomainException('uploadLimitExceeded', 413, ['limit' => $maxFileUploadSizeInBytes / 1024]);
        } else {
            throw new DomainException('uploadFailed', 500);
        }
    }
}
