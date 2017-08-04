<?php
namespace Repeka\Application\ParamConverter;

use Repeka\Application\ParamConverter\MetadataValueProcessor\MetadataValueProcessor;
use Repeka\Application\Upload\FilesystemDriver;
use Repeka\Application\Upload\ResourceFilePathGenerator;
use Repeka\Domain\Exception\DomainException;
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
    /** @var ResourceFilePathGenerator */
    private $pathGenerator;
    /** @var FilesystemDriver */
    private $filesystemDriver;
    /** @var MetadataValueProcessor */
    private $metadataValueProcessor;

    public function __construct(
        MetadataRepository $metadataRepository,
        ResourceRepository $resourceRepository,
        ResourceFilePathGenerator $pathGenerator,
        FilesystemDriver $filesystemDriver,
        MetadataValueProcessor $metadataValueProcessor
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceRepository = $resourceRepository;
        $this->pathGenerator = $pathGenerator;
        $this->filesystemDriver = $filesystemDriver;
        $this->metadataValueProcessor = $metadataValueProcessor;
    }

    public function apply(Request $request, ParamConverter $configuration): bool {
        $contents = $this->getContentsFromRequest($request);
        $request->attributes->set($configuration->getName(), $contents);
        return true;
    }

    /** @inheritdoc */
    public function supports(ParamConverter $configuration): bool {
        return true;
    }

    private function getContentsFromRequest(Request $request): array {
        $contents = [];
        $contentsFromRequest = $request->get('contents');
        if (!$contentsFromRequest) {
            $this->handleEmptyRequestContents($request);
        }
        foreach (json_decode($contentsFromRequest) ?? [] as $metadataId => $values) {
            $baseMetadata = $this->metadataRepository->findOne($metadataId);
            $contents[$metadataId] = $this->metadataValueProcessor->process($values, $baseMetadata->getControl(), $request);
        }
        return $contents;
    }

    /**
     * Tries to detect if the max upload size has been reached and throw appropriate exception then.
     *
     * @see http://stackoverflow.com/a/2133726/878514 how to detect upload limit reached?
     * @see http://stackoverflow.com/a/4445721/878514 how to get the max upload limit for current request?
     * @see http://ca2.php.net/manual/en/ini.core.php#66801 idea of comparison to $_SERVER['CONTENT_LENGTH']
     */
    private function handleEmptyRequestContents(Request $request) {
        $maxFileUploadSize = ini_get('post_max_size');
        $maxFileUploadSizeInBytes = (int)(str_replace('M', '', $maxFileUploadSize) * 1024 * 1024);
        $actualUploadSize = $request->server->get('CONTENT_LENGTH');
        if ($actualUploadSize > $maxFileUploadSizeInBytes) {
            throw new DomainException('uploadLimitExceeded', 413, ['limit' => $maxFileUploadSize]);
        } else {
            throw new DomainException('uploadFailed', 500);
        }
    }
}
