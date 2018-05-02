<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\UseCase\Audit\AuditedCommandNamesQuery;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditController extends ApiController {
    /**
     * @Route("/audit")
     * @Method("GET")
     */
    public function getListAction(Request $request) {
        $queryBuilder = AuditEntryListQuery::builder();
        $commandNames = $request->get('commandNames', []);
        $contentsFilter = json_decode($request->get('resourceContents', '{}'), true);
        Assertion::isArray($commandNames);
        $queryBuilder->filterByCommandNames($commandNames)->filterByResourceContents(is_array($contentsFilter) ? $contentsFilter : []);
        if ($request->query->has('page')) {
            $page = $request->query->get('page', 1);
            $resultsPerPage = $request->query->get('resultsPerPage', 10);
            $queryBuilder->setPage($page)->setResultsPerPage($resultsPerPage);
        }
        $entries = $this->handleCommand($queryBuilder->build());
        return $this->createPageResponse(
            $entries,
            Response::HTTP_OK,
            [
                'customColumns' => $request->get('customColumns', []),
            ]
        );
    }

    /**
     * @Route("/audit-commands")
     * @Method("GET")
     */
    public function getAuditCommandsAction() {
        $commandNames = $this->handleCommand(new AuditedCommandNamesQuery());
        return $this->createJsonResponse($commandNames);
    }
}
