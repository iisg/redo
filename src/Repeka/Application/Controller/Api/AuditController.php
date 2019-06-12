<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\UseCase\Audit\AuditedCommandNamesQuery;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Domain\UseCase\Audit\AuditEntryListQueryBuilder;
use Repeka\Domain\UseCase\Audit\AuditExportToCsvCommand;
use Repeka\Domain\UseCase\Stats\StatisticsQuery;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/audit")
 */
class AuditController extends ApiController {
    /**
     * @Route
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function getListAction(Request $request) {
        $auditEntryListQuery = $this->getAuditEntryListQuery($request)->build();
        $entries = $this->handleCommand($auditEntryListQuery);
        return $this->createPageResponse(
            $entries,
            Response::HTTP_OK,
            [
                'customColumns' => $request->get('customColumns', []),
            ]
        );
    }

    /**
     * @Route("/export")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function getExportAction(Request $request) {
        $queryBuilder = $this->getAuditEntryListQuery($request);
        $customColumns = $request->get('customColumns', []);
        $auditExportCommand = new AuditExportToCsvCommand($queryBuilder, $customColumns);
        $result = $this->handleCommand($auditExportCommand);
        return $this->createJsonResponse($result);
    }

    private function getAuditEntryListQuery(Request $request): AuditEntryListQueryBuilder {
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
        $users = $request->get('users', []);
        Assertion::isArray($users);
        $queryBuilder->filterByUsers($users);
        $resourceKinds = $request->get('resourceKinds', []);
        Assertion::isArray($resourceKinds);
        $queryBuilder->filterByResourceKinds($resourceKinds);
        $transitions = $request->get('transitions', []);
        Assertion::isArray($transitions);
        $queryBuilder->filterByTransitions($transitions);
        Assertion::isArray($commandNames);
        $queryBuilder->filterByCommandNames($commandNames)
            ->filterByResourceContents(is_array($contentsFilter) ? $contentsFilter : []);
        if ($request->query->has('page')) {
            $page = $request->query->get('page', 1);
            $resultsPerPage = $request->query->get('resultsPerPage', 10);
            $queryBuilder->setPage($page)->setResultsPerPage($resultsPerPage);
        }
        return $queryBuilder;
    }

    /**
     * @Route("/commands")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function getAuditCommandsAction(Request $request) {
        $onlyResource = $request->query->get('onlyResource', false);
        $commandNames = $this->handleCommand(new AuditedCommandNamesQuery($onlyResource));
        return $this->createJsonResponse($commandNames);
    }

    /**
     * @Route("/statistics")
     * @Method("GET")
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function getStatisticsAction(Request $request) {
        $queryBuilder = StatisticsQuery::builder();
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
        $tasks = $this->handleCommand($queryBuilder->build());
        return $this->createJsonResponse($tasks);
    }
}
