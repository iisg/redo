<?php
namespace Repeka\Application\Twig;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Twig_Error_Loader;
use Twig_Source;

class ResourcesTwigLoader implements \Twig_LoaderInterface {
    const TEMPLATE_METADATA_KIND_NAME = 'Imported file template';

    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var string */
    private $templatesResourceClass;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var LanguageRepository */
    private $languageRepository;

    public function __construct(
        ResourceKindRepository $resourceKindRepository,
        MetadataRepository $metadataRepository,
        LanguageRepository $languageRepository,
        ?string $templatesResourceClass
    ) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->templatesResourceClass = $templatesResourceClass;
        $this->metadataRepository = $metadataRepository;
        $this->languageRepository = $languageRepository;
    }

    public function getTemplatesResourceClass(): ?string {
        return $this->templatesResourceClass;
    }

    public function getTemplateMetadata(): ?Metadata {
        if ($this->templatesResourceClass) {
            try {
                return $this->metadataRepository->findByName(self::TEMPLATE_METADATA_KIND_NAME, $this->templatesResourceClass);
            } catch (\Exception $e) {
            }
        }
        return null;
    }

    public function getTemplateContent(ResourceKind $templateResourceKind): ?string {
        return $templateResourceKind->getMetadataByName(self::TEMPLATE_METADATA_KIND_NAME)->getConstraints()['displayStrategy'] ?? null;
    }

    public function getSourceContext($name) {
        $resourceKind = $this->getTemplateResourceKind($name);
        if (!$resourceKind) {
            throw new Twig_Error_Loader(sprintf('Template "%s" does not exist.', $name));
        }
        return new Twig_Source($this->getTemplateContent($resourceKind), $name);
    }

    public function exists($name) {
        if (is_string($name)) {
            $templateResourceKind = $this->getTemplateResourceKind($name);
            return $templateResourceKind && $this->getTemplateContent($templateResourceKind);
        } else {
            return false;
        }
    }

    public function getCacheKey($name) {
        return $name;
    }

    /** @inheritdoc */
    public function isFresh($name, $time) {
        return false;
    }

    private function getFirstAvailableLanguageCode(): string {
        return $this->languageRepository->getAvailableLanguageCodes()[0];
    }

    public function getTemplateResourceKind(string $name): ?ResourceKind {
        if ($templateMetadata = $this->getTemplateMetadata()) {
            $query = ResourceKindListQuery::builder()
                ->filterByResourceClass($templateMetadata->getResourceClass())
                ->filterByName([$this->getFirstAvailableLanguageCode() => $name])
                ->setPage(1)
                ->setResultsPerPage(1)
                ->build();
            $resourceKinds = $this->resourceKindRepository->findByQuery($query);
            return $resourceKinds[0] ?? null;
        }
        return null;
    }
}
