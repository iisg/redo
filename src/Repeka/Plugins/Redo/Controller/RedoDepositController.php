<?php
namespace Repeka\Plugins\Redo\Controller;

use Assert\Assertion;
use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\ReproductorPermissionHelper;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggedPath;
use Repeka\Plugins\WorkflowPlaceTagger\Model\WorkflowPlaceTaggerHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RedoDepositController extends Controller {
    use CommandBusAware;
    use CurrentUserAware;

    /** @var NormalizerInterface */
    private $normalizer;
    /** @var ReproductorPermissionHelper */
    private $reproductorPermissionHelper;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var WorkflowPlaceTaggerHelper */
    private $workflowPlaceTaggerHelper;
    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;

    public function __construct(
        NormalizerInterface $normalizer,
        ReproductorPermissionHelper $reproductorPermissionHelper,
        ResourceRepository $resourceRepository,
        WorkflowPlaceTaggerHelper $workflowPlaceTaggerHelper,
        ResourceContentsAdjuster $resourceContentsAdjuster
    ) {
        $this->normalizer = $normalizer;
        $this->reproductorPermissionHelper = $reproductorPermissionHelper;
        $this->resourceRepository = $resourceRepository;
        $this->workflowPlaceTaggerHelper = $workflowPlaceTaggerHelper;
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
    }

    /**
     * @Route("/deposit", name="deposit")
     * @Template("redo/deposit/choose-resource-kind.twig")
     */
    public function chooseResourceKindAction(Request $request) {
        if ($request->isMethod(Request::METHOD_POST)) {
            $id = $request->get('resourceKindId');
            if ($id) {
                return $this->redirectToRoute('depositChooseParent', ['resourceKind' => $id]);
            }
        }
        $resourceKinds = $this->reproductorPermissionHelper->getResourceKindsWhichResourcesUserCanCreate($this->getCurrentUser());
        $resourceKinds = array_values(
            array_filter(
                $resourceKinds,
                function (ResourceKind $resourceKind) {
                    try {
                        if ($resourceKind->hasWorkflow()) {
                            new WorkflowPlaceTaggedPath('deposit', $resourceKind->getWorkflow(), $this->workflowPlaceTaggerHelper);
                            return true;
                        }
                    } catch (\InvalidArgumentException $e) {
                    }
                    return false;
                }
            )
        );
        return ['resourceKinds' => $resourceKinds];
    }

    /**
     * @Route("/deposit/{resourceKind}", name="depositChooseParent")
     * @Template("redo/deposit/choose-parent.twig")
     * @Security("is_granted('VIEW', resourceKind)")
     */
    public function chooseParentAction(ResourceKind $resourceKind, Request $request) {
        $depositPath = new WorkflowPlaceTaggedPath('deposit', $resourceKind->getWorkflow(), $this->workflowPlaceTaggerHelper);
        if ($request->isMethod(Request::METHOD_POST) && ($id = $request->get('parentId'))) {
            $allowedCollections = $this->reproductorPermissionHelper->getCollectionsWhereUserIsReproductor(
                $this->getCurrentUser(),
                $resourceKind
            );
            if (!in_array($id, EntityUtils::mapToIds($allowedCollections))) {
                throw $this->createAccessDeniedException();
            }
            $parent = $this->handleCommand(new ResourceQuery($id));
            return FirewallMiddleware::bypass(
                function () use ($depositPath, $parent, $resourceKind) {
                    $resource = new ResourceEntity($resourceKind, ResourceContents::empty());
                    $resource = $this->resourceRepository->save($resource);
                    $contents = $this->resourceContentsAdjuster->adjust(
                        [
                            SystemMetadata::PARENT => $parent->getId(),
                            'osoba_tworzaca_rekord' => $this->getUser()->getUserData()->getId(),
                            SystemMetadata::VISIBILITY => $this->getUser()->getUserData()->getId(),
                        ]
                    );
                    $command = ResourceGodUpdateCommand::builder()
                        ->setResource($resource)
                        ->setNewContents($contents)
                        ->changePlaces([$depositPath->getPlaces()[0]->getId()])
                        ->build();
                    $this->handleCommand($command);
                    return $this->redirectToRoute(
                        'depositTransition',
                        ['resource' => $resource->getId(), 'transition' => $depositPath->getTransitions()[0]->getId()]
                    );
                }
            );
        }
        $possibleCollections = $this->reproductorPermissionHelper->getCollectionsWhereUserIsReproductor(
            $this->getCurrentUser(),
            $resourceKind
        );
        return [
            'resourceKind' => $resourceKind,
            'possibleCollections' => $this->normalizer->normalize($possibleCollections),
            'depositPath' => $depositPath,
        ];
    }

    /**
     * @Route("/deposit/{resource}/{transition}", name="depositTransition")
     * @Template("redo/deposit/deposit-form.twig")
     * @Security("is_granted('METADATA_OSOBA_TWORZACA_REKORD', resource)")
     */
    public function depositTransitionFormAction(ResourceEntity $resource, string $transition, Request $request) {
        $transition = $resource->getWorkflow()->getTransition($transition);
        $depositPath = new WorkflowPlaceTaggedPath('deposit', $resource->getWorkflow(), $this->workflowPlaceTaggerHelper);
        Assertion::true($depositPath->hasTransition($transition), 'Invalid transition requested.');
        if ($transition->getFromIds()[0] != $resource->getWorkflow()->getPlaces($resource)[0]->getId()) {
            // we need to go back!
            $resource->getWorkflow()->setCurrentPlaces($resource, $transition->getFromIds());
            $this->resourceRepository->save($resource);
        }
        if ($request->isMethod(Request::METHOD_POST)) {
            $isLastTransition = $transition->getId() == $depositPath->getLastTransition()->getId();
            if ($isLastTransition) {
                $contents = $resource->getContents();
            } else {
                $contents = json_decode($request->get('contents'), true);
            }
            if ($contents && is_array($contents)) {
                $contents = ResourceContents::fromArray($contents);
            }
            if ($contents instanceof ResourceContents) {
                $command = new ResourceTransitionCommand($resource, $contents, $transition);
                /** @var ResourceEntity $resource */
                $resource = $this->handleCommand($command);
                if ($isLastTransition) {
                    $this->addFlash('deposit', 'success');
                    return $this->redirect('/resources/' . $resource->getId());
                } else {
                    return $this->redirectToRoute(
                        'depositTransition',
                        ['resource' => $resource->getId(), 'transition' => $resource->getWorkflow()->getTransitions($resource)[0]->getId()]
                    );
                }
            }
        }
        return [
            'resourceKind' => $resource->getKind(),
            'resource' => $resource,
            'transition' => $transition,
            'depositPath' => $depositPath,
        ];
    }

    /**
     * @Route("/mine", name="myResources")
     * @Template("redo/deposit/deposit-list.twig")
     */
    public function myResourcesAction() {
        $query = ResourceListQuery::builder()
            ->filterByContents(['osoba_tworzaca_rekord' => $this->getCurrentUserOrThrow()->getUserResourceId()])
            ->build();
        $myResources = $this->handleCommand($query);
        return [
            'addedResources' => $myResources,
        ];
    }
}
