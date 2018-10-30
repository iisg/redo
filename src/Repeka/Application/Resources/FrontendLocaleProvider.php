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
            return $localePaths;
        }
    }

    private function getFrontendLocaleList(): array {
        $locales = glob(\AppKernel::APP_PATH . '/../web/admin/res/locales/*', GLOB_ONLYDIR);
        return array_map('basename', $locales);
    }
}
