<?php
namespace Repeka\Application\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ConfigController extends ApiController {
    const PUBLIC_PARAMETERS = [
        'application_version' => 'application_version',
        'default_ui_language' => 'repeka.default_ui_language',
        'supported_controls' => 'repeka.supported_controls',
        'static_permissions' => 'repeka.static_permissions',
    ];

    /**
     * @Route("/config.json")
     */
    public function getConfigAction() {
        return $this->createJsonResponse(array_map([$this, 'getParameter'], self::PUBLIC_PARAMETERS));
    }
}
