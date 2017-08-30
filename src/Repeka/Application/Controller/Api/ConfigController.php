<?php
namespace Repeka\Application\Controller\Api;

use Repeka\Application\Resources\FrontendLocaleProvider;
use Repeka\Application\Upload\UploadSizeHelper;
use Repeka\Application\Validation\ContainerAwareMetadataConstraintManager;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ConfigController extends ApiController {
    const PUBLIC_PARAMETERS = [
        'application_version' => 'application_version',
        'default_ui_language' => 'repeka.default_ui_language',
        'resource_classes' => 'repeka.resource_classes',
        'static_permissions' => 'repeka.static_permissions',
    ];

    /** @var FrontendLocaleProvider */
    private $frontendLocaleProvider;
    /** @var ContainerAwareMetadataConstraintManager */
    private $metadataConstraintManager;

    public function __construct(FrontendLocaleProvider $frontendLocaleProvider, MetadataConstraintManager $metadataConstraintManager) {
        $this->frontendLocaleProvider = $frontendLocaleProvider;
        $this->metadataConstraintManager = $metadataConstraintManager;
    }

    /**
     * @Route("/config.json")
     */
    public function getConfigAction() {
        $parameters = array_map([$this, 'getParameter'], self::PUBLIC_PARAMETERS);
        $uploadSizeHelper = new UploadSizeHelper();
        $response = array_merge($parameters, [
            'control_constraints' => $this->metadataConstraintManager->getRequiredConstraintNamesMap(),
            'supported_controls' => array_values(MetadataControl::toArray()),
            'supported_ui_languages' => $this->frontendLocaleProvider->getLocales(),
            'max_upload_size' => [
                'file' => $uploadSizeHelper->getMaxUploadSizePerFile(),
                'total' => $uploadSizeHelper->getMaxUploadSize(),
            ],
        ]);
        return $this->createJsonResponse($response);
    }
}
