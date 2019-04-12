<?php
namespace Repeka\Plugins\PdfGenerator\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class RepekaPdfGeneratorPluginExtension extends ConfigurableExtension implements PrependExtensionInterface {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('pdf-generator-configuration.yml');
        $container->setParameter('repeka_plugin_pdf_generator.target_resource_directory_id', $mergedConfig['targetResourceDirectoryId']);
        $container->setParameter('repeka_plugin_pdf_generator.wk_html_to_pdf_path', $mergedConfig['wkHtmlToPdfPath']);
    }

    public function prepend(ContainerBuilder $container) {
        $container->prependExtensionConfig(
            'knp_snappy',
            [
                'temporary_folder' => \AppKernel::VAR_PATH . "/cache/snappy",
                'pdf' => ['enabled' => true, 'binary' => '/usr/local/bin/wkhtmltopdf'],
                'image' => ['enabled' => true, 'binary' => '/usr/local/bin/wkhtmltoimage'],
            ]
        );
    }
}
