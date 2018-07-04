<?php
namespace Repeka\Plugins\Ocr\Controller;

use Assert\Assertion;
use Repeka\Application\Controller\Api\ApiController;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\ParamConverter\ResourceContentsParamConverter;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\Workflow\ResourceWorkflowPlugins;
use Repeka\Plugins\Ocr\Model\RepekaOcrResourceWorkflowPlugin;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class RepekaOcrController extends ApiController {

    /** @var ResourceContentsParamConverter */
    private $contentsParamConverter;
    /** @var ResourceWorkflowPlugins */
    private $resourceWorkflowPlugins;

    public function __construct(ResourceWorkflowPlugins $resourceWorkflowPlugins, ResourceContentsParamConverter $contentsParamConverter) {
        $this->contentsParamConverter = $contentsParamConverter;
        $this->resourceWorkflowPlugins = $resourceWorkflowPlugins;
    }

    /**
     * @Route("/resources/{resource}", methods={"GET"})
     * TODO handle upload files instead of hardcoded metadata values OCR is ready
     */
    public function receiveOcredFilesAction(ResourceEntity $resource, Request $request) {
        $metadataForResult = $request->get('metadataForResult');
        $pluginConfigs = $this->resourceWorkflowPlugins->getPluginsConfig(
            $resource->getWorkflow()->getPlaces($resource),
            $resource->getWorkflow()
        );
        $targetConfig = array_filter(
            $pluginConfigs,
            function (ResourceWorkflowPlacePluginConfiguration $config) use ($metadataForResult) {
                return $config->isForPlugin(RepekaOcrResourceWorkflowPlugin::class)
                    && $config->getConfigValue('metadataForResult') == $metadataForResult;
            }
        );
        $targetConfig = $targetConfig[0] ?? null;
        Assertion::notNull($targetConfig, 'Unknown plugin config.');
        $content = $resource->getContents();
        $metadata = $resource->getKind()->getMetadataByIdOrName($metadataForResult);
        $content = $content->withMergedValues($metadata, 'OCRed!');
        $content = $this->contentsParamConverter->processMetadataValues($content, $request);
        $this->handleCommandBypassingFirewall(new ResourceUpdateContentsCommand($resource, $content));
        $transitionToExecute = $targetConfig->getConfigValue('transitionAfterOcr');
        if ($transitionToExecute) {
            $possibleTransitions = $resource->getWorkflow()->getTransitions($resource);
            foreach ($possibleTransitions as $transition) {
                if (in_array($transitionToExecute, $transition->getLabel())) {
                    $this->handleCommandBypassingFirewall(
                        new ResourceTransitionCommand($resource, $resource->getContents(), $transition->getId())
                    );
                    break;
                }
            }
        }
        return $this->createJsonResponse(['success' => true]);
    }

    private function handleCommandBypassingFirewall(Command $command) {
        return FirewallMiddleware::bypass(
            function () use ($command) {
                return $this->handleCommand($command);
            }
        );
    }
}
