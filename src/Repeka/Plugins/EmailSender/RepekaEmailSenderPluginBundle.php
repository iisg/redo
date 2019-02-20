<?php
namespace Repeka\Plugins\EmailSender;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaEmailSenderPluginBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $env = $container->getParameter("kernel.environment");
        if ($env === 'test') {
            $container->addCompilerPass(
                new \Repeka\Plugins\EmailSender\Tests\Integration\TestEmailCompilerPass()
            );
        }
        parent::build($container);
    }
}
