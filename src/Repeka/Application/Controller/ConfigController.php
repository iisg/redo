<?php
namespace Repeka\Application\Controller;

use Repeka\Application\Authentication\UserDataMapping;
use Repeka\Application\Resources\FrontendLocaleProvider;
use Repeka\Application\Upload\UploadSizeHelper;
use Repeka\Application\Validation\ContainerAwareMetadataConstraintManager;
use Repeka\Domain\MetadataImport\Mapping\Mapping;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ConfigController extends ApiController {
    const PUBLIC_PARAMETERS = [
        'application_name' => 'repeka.application_name',
        'application_version' => 'repeka.version',
        'default_ui_language' => 'repeka.default_ui_language',
        'resource_classes' => 'repeka.resource_classes',
        'static_permissions' => 'repeka.static_permissions',
    ];

    /** @var FrontendLocaleProvider */
    private $frontendLocaleProvider;
    /** @var ContainerAwareMetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var UserDataMapping */
    private $userDataMapping;

    public function __construct(
        FrontendLocaleProvider $frontendLocaleProvider,
        MetadataConstraintManager $metadataConstraintManager,
        UserDataMapping $userDataMapping
    ) {
        $this->frontendLocaleProvider = $frontendLocaleProvider;
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->userDataMapping = $userDataMapping;
    }

    /**
     * @Route("/config.json")
     */
    public function getConfigAction() {
        $parameters = array_map([$this, 'getParameter'], self::PUBLIC_PARAMETERS);
        $uploadSizeHelper = new UploadSizeHelper();
        if ($this->userDataMapping->mappingExists()) {
            $userMappedMetadataIds = array_map(
                function (Mapping $mapping) {
                    return $mapping->getMetadata()->getId();
                },
                $this->userDataMapping->getImportConfig()->getMappings()
            );
        }
        $response = array_merge(
            $parameters,
            [
                'control_constraints' => $this->metadataConstraintManager->getRequiredConstraintNamesMap(),
                'supported_ui_languages' => $this->frontendLocaleProvider->getLocales(),
                'user_mapped_metadata_ids' => $userMappedMetadataIds ?? [],
                'max_upload_size' => [
                    'file' => $uploadSizeHelper->getMaxUploadSizePerFile(),
                    'total' => $uploadSizeHelper->getMaxUploadSize(),
                ],
            ]
        );
        return $this->createJsonResponse($response);
    }
}
