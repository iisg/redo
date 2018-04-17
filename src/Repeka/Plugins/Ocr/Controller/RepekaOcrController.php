<?php
namespace Repeka\Plugins\Ocr\Controller;

use Repeka\Application\Controller\Api\ApiController;
use Repeka\Application\ParamConverter\ResourceContentsParamConverter;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Plugins\Ocr\Model\RepekaOcrResourceWorkflowPlugin;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class RepekaOcrController extends ApiController {

    /** @var RepekaOcrResourceWorkflowPlugin */
    private $configuration;
    /** @var ResourceContentsParamConverter */
    private $contentsParamConverter;

    public function __construct(RepekaOcrResourceWorkflowPlugin $configuration, ResourceContentsParamConverter $contentsParamConverter) {
        $this->configuration = $configuration;
        $this->contentsParamConverter = $contentsParamConverter;
    }

    /**
     * @Route("/resources/{resource}", methods={"GET"})
     * TODO handle upload files instead of hardcoded metadata values OCR is ready
     */
    public function receiveOcredFilesAction(ResourceEntity $resource, Request $request) {
        $metadataIds = array_filter($this->configuration->getOption('metadataForResult', $resource));
        $content = $resource->getContents();
        foreach ($metadataIds as $metadataId) {
            $metadata = $resource->getKind()->getMetadataByIdOrName($metadataId);
            $content = $content->withMergedValues($metadata, 'OCRed!');
        }
        $content = $this->contentsParamConverter->processMetadataValues($content, $request);
        $this->handleCommand(new ResourceUpdateContentsCommand($resource, $content));
        $transitionToExecute = current(array_filter($this->configuration->getOption('transitionAfterOcr', $resource)));
        if ($transitionToExecute) {
            $possibleTransitions = $resource->getWorkflow()->getTransitions($resource);
            foreach ($possibleTransitions as $transition) {
                if (in_array($transitionToExecute, $transition->getLabel())) {
                    $this->handleCommand(new ResourceTransitionCommand($resource, $resource->getContents(), $transition->getId()));
                    break;
                }
            }
        }
        return $this->createJsonResponse(['success' => true]);
    }
}
