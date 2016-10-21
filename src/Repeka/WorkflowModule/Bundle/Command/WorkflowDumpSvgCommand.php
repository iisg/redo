<?php
namespace Repeka\WorkflowModule\Bundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Workflow\Dumper\GraphvizDumper;

/**
 * Generates SVG diagram of a book workflow. The generated SVG will be served to the admin panel.
 * The diagram needs to be regenerated on every workflow change with `bin/console workflow:dump-svg`.
 *
 * You need to have GraphViz installed and available on classpath in order to generate the diagram.
 *
 * @see http://www.graphviz.org/
 * @see https://goo.gl/KmVyEy
 */
class WorkflowDumpSvgCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('workflow:dump-svg')
            ->setDescription('Generates SVG diagram of a book workflow.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $workflow = $this->getContainer()->get('workflow.book');
        $definition = $this->getProperty($workflow, 'definition');
        $dumper = new GraphvizDumper();
        $dot = $dumper->dump($definition, null, ['node' => ['width' => 1]]);
        $process = new Process('dot -Tsvg');
        $process->setInput($dot);
        $process->mustRun();
        $svg = $process->getOutput();
        $destination = $this->getContainer()->getParameter('kernel.root_dir') . '/../web/files/workflow.svg';
        file_put_contents($destination, $svg);
    }

    private function getProperty($object, $property) {
        $reflectionProperty = new \ReflectionProperty(get_class($object), $property);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($object);
    }
}
