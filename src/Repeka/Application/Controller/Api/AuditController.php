<?php
namespace Repeka\Application\Controller\Api;

use Assert\Assertion;
use Repeka\Domain\Entity\StatisticsBucket;
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
        $queryBuilder = $queryBuilder
            ->filterByDateFrom($request->get('dateFrom', '2000-01-01'))
            ->filterByDateTo($request->get('dateTo', '+1 day'));
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
        $queryBuilder->filterByRegex($request->get('regex', ''));
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
    public function getStatisticsAction(Request $request): Response {
        return $this->createJsonResponse($this->getStatistics($request));
    }

    /**
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     * @return StatisticsBucket[]
     */
    private function getStatistics(Request $request, bool $groupByResources = false): array {
        $queryBuilder = StatisticsQuery::builder()
            ->filterByDateFrom($request->get('dateFrom', '2000-01-01'))
            ->filterByDateTo($request->get('dateTo', '+1 day'))
            ->filterByResourceKinds($request->get('resourceKinds', []))
            ->filterByResourceId($request->get('resourceId', 0))
            ->filterByResourceContents(json_decode($request->get('resourceContents', '{}'), true) ?: [])
            ->filterByEventGroup($request->get('eventGroup', ''))
            ->aggregateBy($request->get('aggregation', 'millennium'))
            ->groupByResources($groupByResources);
        return $this->handleCommand($queryBuilder->build());
    }

    /**
     * @Route("/statistics/csv", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_SOME_CLASS')")
     */
    public function downloadStatisticsAction(Request $request) {
        ini_set('max_execution_time', '2000');
        ini_set('memory_limit', '2G');
        $groupByResources = $request->get('groupByResources', false);
        $buckets = $this->getStatistics($request, $groupByResources);
        $tmpFilename = tempnam(sys_get_temp_dir(), 'stats');
        $headers = ['Event group', 'Event name', 'Bucket', 'Count'];
        if ($groupByResources) {
            $headers = array_merge(['Resource ID', 'Resource label'], $headers);
        }
        $csvh = fopen($tmpFilename, 'w');
        fwrite($csvh, chr(239) . chr(187) . chr(191)); // UTF-8 BOM
        fputcsv($csvh, $headers);
        foreach ($buckets as $bucket) {
            $data = [
                $bucket->getEventGroup(),
                $bucket->getEventName(),
                $bucket->getBucketLabel(),
                $bucket->getCount(),
            ];
            if ($groupByResources) {
                $data = array_merge([$bucket->getResourceId(), $bucket->getResourceLabel()], $data);
            }
            fputcsv($csvh, $data);
        }
        fclose($csvh);
        $filename = sprintf('%s-%s.csv', $this->container->getParameter('repeka.theme'), date('Ymd-His'));
        return $this->file($tmpFilename, $filename)->deleteFileAfterSend(true);
    }
}
