<?php
namespace Repeka\Application\Resources;

use Psr\Log\LoggerInterface;

class FrontendLocaleProvider {
    /** @var LoggerInterface */
    private $logger;
    /** @var string */
    private $defaultUiLanguage;

    public function __construct(LoggerInterface $logger, string $defaultUiLanguage) {
        $this->logger = $logger;
        $this->defaultUiLanguage = $defaultUiLanguage;
    }

    /** @return string[] */
    public function getLocales(): array {
        $localePaths = $this->getFrontendLocaleList();
        if (empty($localePaths)) {
            $fallbackLanguage = $this->defaultUiLanguage;
            $this->logger->error("No frontend locale packages are available, falling back to '$fallbackLanguage'");
            return [$fallbackLanguage];
        } else {
            return array_map('basename', $localePaths);
        }
    }

    private function getFrontendLocaleList(): array {
        $locales = glob('admin/dist/locales/*', GLOB_ONLYDIR);
        if (empty($locales)) {
            $locales = $this->loadBundledLocaleNames();
        }
        return $locales;
    }

    private function loadBundledLocaleNames(): array {
        $contents = file_get_contents('admin/locales.json');
        return json_decode($contents);
    }
}
