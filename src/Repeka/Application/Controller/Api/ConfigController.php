<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Application\Resources\FrontendLocaleProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ConfigController extends ApiController {
    /** @var FrontendLocaleProvider */
    private $frontendLocaleProvider;

    const PUBLIC_PARAMETERS = [
        'application_version' => 'application_version',
        'default_ui_language' => 'repeka.default_ui_language',
        'supported_controls' => 'repeka.supported_controls',
        'static_permissions' => 'repeka.static_permissions',
    ];

    public function __construct(FrontendLocaleProvider $frontendLocaleProvider) {
        $this->frontendLocaleProvider = $frontendLocaleProvider;
    }

    /**
     * @Route("/config.json")
     */
    public function getConfigAction() {
        $parameters = array_map([$this, 'getParameter'], self::PUBLIC_PARAMETERS);
        $response = array_merge($parameters, [
            'supported_ui_languages' => $this->frontendLocaleProvider->getLocales(),
        ]);
        return $this->createJsonResponse($response);
    }
}
