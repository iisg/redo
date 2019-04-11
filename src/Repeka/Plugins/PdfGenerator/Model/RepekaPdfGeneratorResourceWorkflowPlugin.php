<?php
namespace Repeka\Plugins\PdfGenerator\Model;

use Knp\Snappy\Pdf;
use Repeka\Domain\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\Utils\StringUtils;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaPdfGeneratorResourceWorkflowPlugin extends ResourceWorkflowPlugin {

    private $displayStrategyEvaluator;
    private $wkHtmlToPdfPath;
    private $targetResourceDirectoryId;
    private $fileSystemDriver;
    private $resourceRepository;
    private $resourceFileStorage;

    public function __construct(
        string $wkHtmlToPdfPath,
        ResourceDisplayStrategyEvaluator $displayStrategyEvaluator,
        string $targetResourceDirectoryId,
        FileSystemDriver $fileSystemDriver,
        ResourceRepository $resourceRepository,
        ResourceFileStorage $resourceFileStorage
    ) {
        $this->wkHtmlToPdfPath = $wkHtmlToPdfPath;
        $this->displayStrategyEvaluator = $displayStrategyEvaluator;
        $this->targetResourceDirectoryId = $targetResourceDirectoryId;
        $this->fileSystemDriver = $fileSystemDriver;
        $this->resourceRepository = $resourceRepository;
        $this->resourceFileStorage = $resourceFileStorage;
    }

    public function afterEnterPlace(CommandHandledEvent $event, ResourceWorkflowPlacePluginConfiguration $config) {
        $command = $event->getCommand();
        $resource = $command->getResource();
        $targetMetadataName = $config->getConfigValue('targetMetadataName');
        $targetMetadata = $resource->getKind()->getMetadataByIdOrName($targetMetadataName);
        if ($targetMetadata->getControl() == MetadataControl::FILE()) {
            $pdfOutputFileName = $config->getConfigValue('pdfOutputFileName');
            $pdfOutputFileName = $this->displayStrategyEvaluator->render($resource, $pdfOutputFileName);
            $pdfPresentationStrategy = $config->getConfigValue('pdfPresentationStrategy');
            $resourcePath = $this->targetResourceDirectoryId . '/' . $pdfOutputFileName;
            $fileSystemTargetPath = $this->resourceFileStorage->getFileSystemPath($resource, $resourcePath);
            $inputPathParts = pathinfo($pdfOutputFileName);
            $targetPathParts = pathinfo($fileSystemTargetPath);
            $actualFileName = $this->getNonConflictingFileName($fileSystemTargetPath);
            $resourcePath = StringUtils::joinPaths($this->targetResourceDirectoryId, $inputPathParts['dirname'], $actualFileName);
            $fileSystemTargetPath = $targetPathParts["dirname"] . "/" . $actualFileName;
            $renderResult = $this->displayStrategyEvaluator->render($resource, $pdfPresentationStrategy);
            $this->generatePdfOutputFile($renderResult, $fileSystemTargetPath);
            $resourceContents = $resource->getContents();
            $resourceContents = $resourceContents->withMergedValues($targetMetadata, $resourcePath);
            $resource->updateContents($resourceContents);
            $this->resourceRepository->save($resource);
        } else {
            $this->newAuditEntry($event, 'generatingPDFToNonFileMetadataControl', [], false);
        }
    }

    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('targetMetadataName', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('pdfOutputFileName', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('pdfPresentationStrategy', MetadataControl::TEXTAREA()),
        ];
    }

    private function generatePdfOutputFile(string $renderResult, string $finalCompleteTargetPath): void {
        $snappy = new Pdf($this->wkHtmlToPdfPath);
        $snappy->setOption('enable-javascript', true);
        $snappy->setOption('javascript-delay', 1000);
        $snappy->setOption('encoding', 'utf-8');
        $snappy->generateFromHtml($renderResult, $finalCompleteTargetPath);
    }

    private function getNonConflictingFileName(string $fileSystemTargetPath): string {
        $targetPathParts = pathinfo($fileSystemTargetPath);
        $fileVersion = 1;
        $actualFileSystemTargetPath = $fileSystemTargetPath;
        $actualFileName = $targetPathParts["basename"];
        while ($this->fileSystemDriver->exists($actualFileSystemTargetPath)) {
            $actualFileName = $targetPathParts["filename"] . "_" . $fileVersion . "." . $targetPathParts["extension"];
            $actualFileSystemTargetPath = $targetPathParts["dirname"] . "/" . $actualFileName;
            $fileVersion++;
        }
        return $actualFileName;
    }
}
