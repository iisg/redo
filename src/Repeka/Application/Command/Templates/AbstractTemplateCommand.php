<?php
namespace Repeka\Application\Command\Templates;

use Assert\Assertion;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Twig\ResourcesTwigLoader;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;

abstract class AbstractTemplateCommand extends \Symfony\Component\Console\Command\Command {
    use CommandBusAware;

    /** @var ResourcesTwigLoader */
    protected $loader;
    /** @var ResourceClassExistsRule */
    private $classExistsRule;
    /** @var LanguageRepository */
    private $languageRepository;

    /** @required */
    public function setResourceTwigLoader(ResourcesTwigLoader $loader) {
        $this->loader = $loader;
    }

    /** @required */
    public function setResourceClassValidator(ResourceClassExistsRule $classExistsRule) {
        $this->classExistsRule = $classExistsRule;
    }

    /** @required */
    public function setLanguageRepository(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    protected function handleCommandBypassingFirewall(Command $command) {
        return FirewallMiddleware::bypass(
            function () use ($command) {
                return $this->handleCommand($command);
            }
        );
    }

    protected function ensureTemplatesAreConfigured(): void {
        $templatesResourceClass = $this->loader->getTemplatesResourceClass();
        Assertion::notNull(
            $templatesResourceClass,
            'You must set repeka.templates.templates_resource_class option to use this feature.'
        );
        Assertion::true(
            $this->classExistsRule->validate($templatesResourceClass),
            "Resource class $templatesResourceClass does not exist."
        );
        if (!$this->loader->getTemplateMetadata()) {
            $this->handleCommandBypassingFirewall(
                new MetadataCreateCommand(
                    ResourcesTwigLoader::TEMPLATE_METADATA_KIND_NAME,
                    $this->createLabelInEveryLanguage(ResourcesTwigLoader::TEMPLATE_METADATA_KIND_NAME),
                    [],
                    [],
                    MetadataControl::DISPLAY_STRATEGY,
                    $templatesResourceClass
                )
            );
        }
    }

    protected function createLabelInEveryLanguage(string $label): array {
        $availableLanguageCodes = $this->languageRepository->getAvailableLanguageCodes();
        return array_combine($availableLanguageCodes, array_fill(0, count($availableLanguageCodes), $label));
    }

    protected function getTemplatesPath(string $namespace) {
        $templatePath = realpath(\AppKernel::APP_PATH . '/Resources/views/' . $namespace);
        Assertion::directory($templatePath, "Namespace $namespace does not exist.");
        return $templatePath;
    }

    protected function getTemplatePath(string $templateName): string {
        return \AppKernel::APP_PATH . '/Resources/views/' . $templateName;
    }

    protected function getTemplateFromFile(string $templateName): string {
        return file_get_contents(\AppKernel::APP_PATH . '/Resources/views/' . $templateName);
    }

    protected function discoverFilesTemplates(string $namespace): array {
        $templatePath = $this->getTemplatesPath($namespace);
        $fileIterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($templatePath));
        $templates = [];
        foreach ($fileIterator as $file) {
            if (!$file->isDir()) {
                $templates[] = str_replace(
                    '\\',
                    '/',
                    substr(realpath($file->getPathname()), strlen($templatePath) - strlen($namespace))
                );
            }
        }
        return $templates;
    }
}
