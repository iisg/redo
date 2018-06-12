<?php
namespace Repeka\Application\Command;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends ContainerAwareCommand {
    /** @var UserRepository */
    private $userRepository;
    /** @var CommandBus */
    private $commandBus;

    public function __construct(UserRepository $userRepository, CommandBus $commandBus) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->commandBus = $commandBus;
    }

    protected function configure() {
        $this
            ->setName('repeka:create-user')
            ->addArgument('username', InputArgument::OPTIONAL)
            ->addArgument('password', InputArgument::OPTIONAL)
            ->setDescription('Create a user account.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $username = $input->getArgument('username');
        if (!$username) {
            $username = $helper->ask($input, $output, $this->usernameQuestion());
        }
        $password = $input->getArgument('password');
        if (!$password) {
            $password = $helper->ask($input, $output, $this->passwordQuestion());
        }
        $this->saveNewAccount($username, $password);
        $output->writeln("New account has been created.");
    }

    private function usernameQuestion(): Question {
        $question = new Question('Username [admin]: ', 'admin');
        $question->setValidator(
            function ($answer) {
                if (!is_string($answer) || strlen($answer) < 4 || !preg_match('#^[a-z0-9-_]+$#i', $answer)) {
                    throw new \RuntimeException('Invalid username');
                }
                if ($this->userRepository->loadUserByUsername($answer)) {
                    throw new \RuntimeException("User already exists! Choose different username.");
                }
                return $answer;
            }
        );
        return $question;
    }

    private function passwordQuestion(): Question {
        $question = new Question('Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(
            function ($answer) {
                if (!is_string($answer) || strlen($answer) < 4) {
                    throw new \RuntimeException('Password too short.');
                }
                return $answer;
            }
        );
        return $question;
    }

    private function saveNewAccount(string $username, string $plainPassword) {
        FirewallMiddleware::bypass(
            function () use ($plainPassword, $username) {
                $userCreateCommand = new UserCreateCommand($username, $plainPassword);
                $this->commandBus->handle($userCreateCommand);
            }
        );
    }
}
