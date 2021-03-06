<?php
namespace Repeka\Application\Twig;

use Psr\Container\ContainerInterface;
use Repeka\Application\Resources\FrontendLocaleProvider;
use Repeka\Application\Serialization\ResourceNormalizer;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Utils\ArrayUtils;
use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\ConfigurableMetadataConstraint;
use Repeka\Domain\Validation\MetadataConstraints\MetadataConstraintWithoutConfiguration;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\RequestStack;

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
        'fallback_ui_languages' => 'repeka.fallback_ui_languages',
        'resource_classes' => 'repeka.resource_classes',
        'resource_classes_icons' => 'repeka.resource_classes_icons',
        'static_permissions' => 'repeka.static_permissions',
        'metadata_groups' => 'repeka.metadata_groups',
        'audit' => 'repeka.audit',
        'theme' => 'repeka.theme',
    ];

    /** @var FrontendLocaleProvider */
    private $frontendLocaleProvider;
    /** @var MetadataConstraintManager */
    private $metadataConstraintManager;
    /** @var string */
    private $locale;
    /** @var ResourceNormalizer */
    private $resourceNormalizer;
    /** @var iterable|FrontendConfigProvider[] */
    private $frontendConfigs;

    public function __construct(
        FrontendLocaleProvider $frontendLocaleProvider,
        MetadataConstraintManager $metadataConstraintManager,
        ContainerInterface $container,
        RequestStack $requestStack,
        ResourceNormalizer $resourceNormalizer,
        iterable $frontendConfigs
    ) {
        $this->frontendLocaleProvider = $frontendLocaleProvider;
        $this->metadataConstraintManager = $metadataConstraintManager;
        $this->container = $container;
        $this->locale = $requestStack->getCurrentRequest() ? $requestStack->getCurrentRequest()->getLocale() : '';
        $this->resourceNormalizer = $resourceNormalizer;
        $this->frontendConfigs = $frontendConfigs;
    }

    public function getFunctions() {
        return [
            new \Twig_Function('getFrontendConfig', [$this, 'getConfig']),
            new \Twig_Function('getFrontendBundles', [$this, 'getBundles']),
        ];
    }

    public function getConfig(): array {
        $parameters = array_map([$this->container, 'getParameter'], self::PUBLIC_PARAMETERS);
        $config = array_merge(
            $parameters,
            [
                'control_constraints' => $this->getConfigurableMetadataConstraints(),
                'supported_ui_languages' => $this->frontendLocaleProvider->getLocales(),
                'current_ui_language' => $this->locale,
                'user' => $this->getCurrentUserData(),
                'userIp' => $this->getClientIp(),
            ]
        );
        foreach ($this->frontendConfigs as $frontendConfig) {
            $config = array_merge($config, $frontendConfig->getConfig());
        }
        return $config;
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

    private function getConfigurableMetadataConstraints() {
        $constraints = ArrayUtils::combineArrayWithSingleValue(MetadataControl::toArray(), []);
        foreach ($this->metadataConstraintManager->getConstraints() as $constraint) {
            if ($constraint instanceof ConfigurableMetadataConstraint) {
                foreach ($constraint->getSupportedControls() as $supportedControl) {
                    $constraints[$supportedControl][] = [
                        'name' => $constraint->getConstraintName(),
                        'hasConfiguration' => !($constraint instanceof MetadataConstraintWithoutConfiguration),
                    ];
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
        $request = $this->container->get('request_stack')->getCurrentRequest();
        return $request ? $request->getClientIp() : '';
    }
}
