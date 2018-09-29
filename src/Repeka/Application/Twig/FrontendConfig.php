<?php
namespace Repeka\Application\Twig;

use Psr\Container\ContainerInterface;
use Repeka\Application\Authentication\UserDataMapping;
use Repeka\Application\Resources\FrontendLocaleProvider;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Application\Upload\UploadSizeHelper;
use Repeka\Application\Validation\ContainerAwareMetadataConstraintManager;
use Repeka\Domain\Metadata\MetadataImport\Mapping\Mapping;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class FrontendConfig extends \Twig_Extension {
    use ContainerAwareTrait;
    use CurrentUserAware;

    private const PUBLIC_PARAMETERS = [
        'application_name' => 'applicationName',
        'application_version' => 'repeka.version',
        'default_ui_language' => 'repeka.default_ui_language',
        'resource_classes' => 'repeka.resource_classes',
        'resource_classes_icons' => 'repeka.resource_classes_icons',
        'static_permissions' => 'repeka.static_permissions',
        'metadata_groups' => 'repeka.metadata_groups',
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
        UserDataMapping $userDataMapping,
        ContainerInterface $container
    ) {
        $this->frontendLocaleProvider = $frontendLocaleProvider;
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->userDataMapping = $userDataMapping;
        $this->container = $container;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('getFrontendConfig', [$this, 'getConfig']),
        ];
    }

    public function getConfig() {
        $parameters = array_map([$this->container, 'getParameter'], self::PUBLIC_PARAMETERS);
        $uploadSizeHelper = new UploadSizeHelper();
        if ($this->userDataMapping->mappingExists() && $this->getCurrentUser()) {
            $userMappedMetadataIds = array_map(
                function (Mapping $mapping) {
                    return $mapping->getMetadata()->getId();
                },
                $this->userDataMapping->getImportConfig()->getMappings()
            );
        }
        return array_merge(
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
    }
}
