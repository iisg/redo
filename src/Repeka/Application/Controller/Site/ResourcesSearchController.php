<?php
namespace Repeka\Application\Controller\Site;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class ResourcesSearchController extends Controller {
    use CommandBusAware;

    public function searchResourcesAction(
        string $template,
        array $searchableMetadataNamesOrIds,
        array $searchableResourceClasses,
        array $headers,
        Request $request
    ) {
        $phrase = $request->get('phrase');
        $query = new ResourceListFtsQuery($phrase, $searchableMetadataNamesOrIds, $searchableResourceClasses);
        $results = $this->handleCommand($query);
        $response = $this->render($template, ['results' => $results, 'phrase' => $phrase]);
        $response->headers->add($headers);
        return $response;
    }
}
