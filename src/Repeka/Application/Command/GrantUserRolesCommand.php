<?php
namespace Repeka\Application\Command;

use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserGrantRolesCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GrantUserRolesCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('repeka:grant-user-roles')
            ->setDescription('Evaluates every user and grants its roles based on the rules defined in the configuration file.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $userRepository = $this->getContainer()->get(UserRepository::class);
        $commandBus = $this->getContainer()->get(CommandBus::class);
        $users = $userRepository->findAll();
        $progressBar = new ProgressBar($output, count($users));
        $output->writeln('Granting user roles.');
        $progressBar->start();
        foreach ($users as $user) {
            $commandBus->handle(new UserGrantRolesCommand($user));
            $progressBar->advance();
        }
        $progressBar->clear();
    }
}
