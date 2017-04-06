<?php
namespace Repeka\Application\ParamConverter;

use KHerGe\JSON\JSON;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ResourceContentsParamConverter implements ParamConverterInterface {
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var ResourceRepository */
    private $resourceRepository;
    private $uploadPath;

    public function __construct(
        MetadataRepository $metadataRepository,
        ResourceRepository $resourceRepository,
        ContainerInterface $container
    ) {
        $this->metadataRepository = $metadataRepository;
        $this->resourceRepository = $resourceRepository;
        $this->uploadPath = $container->getParameter('repeka.upload_dir');
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
        $json = new JSON();
        $contentsFromRequest = $request->get('contents');
        if (!$contentsFromRequest) {
            $this->handleEmptyRequestContents($request);
        }
        foreach ($json->decode($contentsFromRequest) ?? [] as $metadataId => $value) {
            $baseMetadata = $this->metadataRepository->findOne($metadataId);
            if ($baseMetadata->getControl() === 'relationship') {
                $relatedResource = $this->resourceRepository->findOne($value);
                $contents[$metadataId] = $relatedResource;
            } elseif ($baseMetadata->getControl() === 'file') {
                $file = $request->files->get($value);
                if ($file) {
                    $fileName = $file->getClientOriginalName();
                    $directoryName = md5(uniqid());
                    $pathToFile = $this->uploadPath . '/' . $directoryName;
                    if (!file_exists($pathToFile)) {
                        mkdir($pathToFile, 0660, true);
                    }
                    $file->move($pathToFile, $fileName);
                    $contents[$metadataId] = $directoryName . '/' . $fileName;
                } else {
                    $contents[$metadataId] = $value;
                }
            } else {
                $contents[$metadataId] = $value;
            }
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
            throw new DomainException("Max upload limit per request is $maxFileUploadSize");
        } else {
            throw new DomainException("Could not save the resource. Please try again later.");
        }
    }
}
