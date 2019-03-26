<?php
namespace Repeka\Plugins\Redo\Controller;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RedoReportRemarkController extends Controller {
    use CommandBusAware;
    use CurrentUserAware;

    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var MetadataRepository */
    private $metadataRepository;

    public function __construct(ResourceKindRepository $resourceKindRepository, MetadataRepository $metadataRepository) {
        $this->resourceKindRepository = $resourceKindRepository;
        $this->metadataRepository = $metadataRepository;
    }

    /**
     * @Route("/report", name="report")
     * @Template("redo/report/report-form.twig")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function reportRemarkAction(Request $request) {
        if ($request->isMethod(Request::METHOD_POST)) {
            $parentId = $request->request->get('parentId');
            /** @var ResourceEntity $parent */
            $parent = $this->handleCommand(new ResourceQuery($parentId));
            $this->denyAccessUnlessGranted('METADATA_TEASER_VISIBILITY', $parent);
            return FirewallMiddleware::bypass(
                function () use ($parent, $request) {
                    $resourceKind = $this->resourceKindRepository->findByName('zgloszona_uwaga');
                    $currentUser = $this->getUser();
                    $emailAddress = $request->request->get('email_address');
                    $remarkTitle = $request->request->get('remark_title');
                    $remarkContent = $request->request->get('remark_content');
                    $contents = ResourceContents::fromArray(
                        [
                            SystemMetadata::PARENT => $parent->getId(),
                            SystemMetadata::REPRODUCTOR => $parent->getContents()->getValues(SystemMetadata::REPRODUCTOR),
                            $this->metadataRepository->findByName('email_address')->getId() => $emailAddress,
                            $this->metadataRepository->findByName('remark_title')->getId() => $remarkTitle,
                            $this->metadataRepository->findByName('remark_content')->getId() => $remarkContent,
                        ]
                    );
                    $command = new ResourceCreateCommand($resourceKind, $contents, $currentUser);
                    /** @var ResourceEntity $resource */
                    $resource = $this->handleCommand($command);
                    $transition = $resource->getWorkflow()->getTransitionsFromPlace($resource->getCurrentPlaces()[0])[0];
                    $command = new ResourceTransitionCommand($resource, $resource->getContents(), $transition);
                    /** @var ResourceEntity $resource */
                    $this->handleCommand($command);
                    $this->addFlash('report', 'success');
                }
            );
        }
    }
}
