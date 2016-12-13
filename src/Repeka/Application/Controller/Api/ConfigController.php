<?php
namespace Repeka\Application\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ConfigController extends ApiController {
    const PUBLIC_PARAMETERS = [
        'application_version' => 'application_version',
        'supported_controls' => 'data_module.supported_controls',
    ];

    /**
     * @Route("/config.json")
     */
    public function getConfigAction() {
        return $this->createJsonResponse(array_map([$this, 'getParameter'], self::PUBLIC_PARAMETERS));
    }
}
