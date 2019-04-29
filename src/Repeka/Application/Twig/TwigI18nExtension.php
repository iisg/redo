<?php
namespace Repeka\Application\Twig;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Utils\PrintableArray;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TwigI18nExtension extends \Twig_Extension {
    use CommandBusAware;

    /** @var Request */
    private $request;
    /** @var MetadataRepository */
    private $metadataRepository;
    /** @var FrontendConfig */
    private $frontendConfig;

    public function __construct(
        RequestStack $requestStack,
        MetadataRepository $metadataRepository,
        FrontendConfig $frontendConfig
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->metadataRepository = $metadataRepository;
        $this->frontendConfig = $frontendConfig;
    }

    public function getFilters() {
        return [
            new \Twig_Filter('inCurrentLanguage', [$this, 'inCurrentLanguage']),
            new \Twig_Filter('onlyMetadataInCurrentLanguage', [$this, 'onlyMetadataValuesInCurrentLanguage']),
        ];
    }

    public function inCurrentLanguage($value) {
        $locales = $this->getLocalesOrder();
        if (is_array($value)) {
            foreach ($locales as $locale) {
                if (array_key_exists($locale, $value)) {
                    return $value[$locale];
                }
            }
            return current($value);
        }
        return $value;
    }

    private function getLocalesOrder() {
        $requestedLocale = strtoupper($this->request->getLocale());
        $locales = $this->frontendConfig->getConfig()['fallback_ui_languages'];
        array_unshift($locales, $requestedLocale);
        return array_map('strtoupper', $locales);
    }

    /**
     * @SuppressWarnings("PHPMD.CyclomaticComplexity")
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    public function onlyMetadataValuesInCurrentLanguage(iterable $metadataValues, Metadata $metadata, bool $returnFirstIfEmpty = false) {
        $locale = strtoupper($this->request->getLocale());
        $submetadataIdsToCheck = $this->getSystemLanguageSubmetadataIds($metadata);
        if (empty($submetadataIdsToCheck)) {
            // metadata does not have system language submetadata, so there's nothing to remove
            return $metadataValues;
        }
        $indexesToLeave = [];
        /** @var MetadataValue $metadataValue */
        foreach ($metadataValues as $valueIndex => $metadataValue) {
            /** @var MetadataValue[] $submetadataValues */
            foreach ($metadataValue->getSubmetadata() as $submetadataId => $submetadataValues) {
                if (array_key_exists($submetadataId, $submetadataIdsToCheck)
                    && $this->anyMetadataValueContains($submetadataValues, $locale)) {
                    $indexesToLeave[] = $valueIndex;
                    break;
                }
            }
        }
        $result = [];
        foreach ($indexesToLeave as $index) {
            $result[] = $metadataValues[$index];
        }
        if (!$result && $returnFirstIfEmpty) {
            $result[] = current(iterator_to_array($metadataValues));
        }
        return new PrintableArray($result);
    }

    private function getSystemLanguageSubmetadataIds(Metadata $metadata): array {
        $query = MetadataListQuery::builder()
            ->filterByParent($metadata)
            ->filterByControl(MetadataControl::SYSTEM_LANGUAGE())
            ->build();
        $systemLanguageMetadataList = $this->metadataRepository->findByQuery($query);
        return array_flip(EntityUtils::mapToIds($systemLanguageMetadataList));
    }

    /** @param $metadataValueList MetadataValue[] */
    private function anyMetadataValueContains($metadataValueList, $value): bool {
        foreach ($metadataValueList as $metadataValue) {
            if ($metadataValue->getValue() === $value) {
                return true;
            }
        }
        return false;
    }
}
