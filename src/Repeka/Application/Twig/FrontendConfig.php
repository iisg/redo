<?php
namespace Repeka\Application\Twig;

use Psr\Container\ContainerInterface;
use Repeka\Application\Authentication\UserDataMapping;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Resources\FrontendLocaleProvider;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Metadata\MetadataImport\Mapping\Mapping;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\ConfigurableMetadataConstraint;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var UserDataMapping */
    private $userDataMapping;
    /** @var ResourceNormalizer */
    private $resourceNormalizer;

    public function __construct(
        FrontendLocaleProvider $frontendLocaleProvider,
        MetadataConstraintManager $metadataConstraintManager,
        UserDataMapping $userDataMapping,
        ContainerInterface $container,
        ResourceNormalizer $resourceNormalizer
    ) {
        $this->frontendLocaleProvider = $frontendLocaleProvider;
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->userDataMapping = $userDataMapping;
        $this->container = $container;
        $this->resourceNormalizer = $resourceNormalizer;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('getFrontendConfig', [$this, 'getConfig']),
            new \Twig_Function('getFrontendBundles', [$this, 'getBundles']),
        ];
    }

    public function getConfig(): array {
        $parameters = array_map([$this->container, 'getParameter'], self::PUBLIC_PARAMETERS);
        if ($this->userDataMapping->mappingExists() && $this->getCurrentUser()) {
            $userMappedMetadataIds = $this->getUserMappedMetadataIds();
        }
        return array_merge(
            $parameters,
            [
                'control_constraints' => $this->getConfigurableMetadataConstraints(),
                'supported_ui_languages' => $this->frontendLocaleProvider->getLocales(),
                'user_mapped_metadata_ids' => $userMappedMetadataIds ?? [],
                'user' => $this->getCurrentUserData(),
                'userIp' => $this->getClientIp(),
            ]
        );
    }

    public function getBundles(): array {
        $bundles = glob(\AppKernel::APP_PATH . '/../web/admin/bundles/*');
        $bundleNames = array_map('basename', $bundles);
        $bundlesToInclude = array_filter(
            $bundleNames,
            function (string $bundleName) {
                return strpos($bundleName, 'cytoscape') === false;
            }
        );
        return $bundlesToInclude;
    }

    private function getUserMappedMetadataIds() {
        return FirewallMiddleware::bypass(
            function () {
                return array_map(
                    function (Mapping $mapping) {
                        return $mapping->getMetadata()->getId();
                    },
                    $this->userDataMapping->getImportConfig()->getMappings()
                );
            }
        );
    }

    private function getConfigurableMetadataConstraints() {
        $constraints = ArrayUtils::combineArrayWithSingleValue(MetadataControl::toArray(), []);
        foreach ($this->metadataConstraintManager->getConstraints() as $constraint) {
            if ($constraint instanceof ConfigurableMetadataConstraint) {
                foreach ($constraint->getSupportedControls() as $supportedControl) {
                    $constraints[$supportedControl][] = $constraint->getConstraintName();
                }
            }
        }
        return $constraints;
    }

    private function getCurrentUserData() {
        $user = $this->getCurrentUser();
        if ($user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'userData' => $this->resourceNormalizer->normalize($user->getUserData()),
                'roles' => $user->getRoles(),
                'groupsIds' => $user->getUserGroupsIds(),
            ];
        } else {
            return new \stdClass();
        }
    }

    private function getClientIp() {
        $request = $this->container->get('request_stack')->getMasterRequest();
        return $request ? $request->getClientIp() : '';
    }
}
