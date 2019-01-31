<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\UseCase\Audit\AuditedCommandNamesQuery;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditController extends ApiController {
    /**
     * @Route("/audit")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function getListAction(Request $request) {
        $queryBuilder = AuditEntryListQuery::builder();
        $commandNames = $request->get('commandNames', []);
        $contentsFilter = json_decode($request->get('resourceContents', '{}'), true);
        if ($request->query->has('resourceId')) {
            $resourceId = intval($request->query->get('resourceId', 0));
            $queryBuilder = $queryBuilder->filterByResourceId($resourceId);
        }
        if ($request->query->has('dateFrom')) {
            Assertion::date($request->get('dateFrom'), 'Y-m-d\TH:i:s');
            $dateFrom = $request->get('dateFrom');
            $queryBuilder->filterByDateFrom($dateFrom);
        }
        if ($request->query->has('dateTo')) {
            Assertion::date($request->get('dateTo'), 'Y-m-d\TH:i:s');
            $dateTo = $request->get('dateTo');
            $queryBuilder->filterByDateTo($dateTo);
        }
        Assertion::isArray($commandNames);
        $queryBuilder->filterByCommandNames($commandNames)
            ->filterByResourceContents(is_array($contentsFilter) ? $contentsFilter : []);
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
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function getAuditCommandsAction(Request $request) {
        $onlyResource = $request->query->get('onlyResource', false);
        $commandNames = $this->handleCommand(new AuditedCommandNamesQuery($onlyResource));
        return $this->createJsonResponse($commandNames);
    }
}
