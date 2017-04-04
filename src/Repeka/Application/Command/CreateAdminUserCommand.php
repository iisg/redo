<?php
namespace Repeka\Application\Command;

use Repeka\Application\Entity\UserEntity;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateAdminUserCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('repeka:create-admin-user')
            ->setDescription('Create user account for administrator.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $username = $helper->ask($input, $output, $this->usernameQuestion());
        $password = $helper->ask($input, $output, $this->passwordQuestion());
        $this->saveNewAdminAccount($username, $password);
        $output->writeln("New admin account has been created.");
    }

    private function usernameQuestion():Question {
        $question = new Question('New admin\'s username [admin]: ', 'admin');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || strlen($answer) < 4 || !preg_match('#^[a-z0-9-_]+$#i', $answer)) {
                throw new \RuntimeException('Invalid username');
            }
            if ($this->getContainer()->get('repository.user')->findOneBy(['username' => $answer])) {
                throw new \RuntimeException("User already exists! Choose different username.");
            }
            return $answer;
        });
        return $question;
    }

    private function passwordQuestion():Question {
        $question = new Question('Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || strlen($answer) < 4) {
                throw new \RuntimeException('Password too short.');
            }
            return $answer;
        });
        return $question;
    }

    private function saveNewAdminAccount(string $username, string $password) {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $passwordEncoder = $this->getContainer()->get('security.password_encoder');
        $user = new UserEntity();
        $user->setFirstname('Administrator');
        $user->setLastname('created ' . date('Y-m-d H:i'));
        $user->setEmail($username . '@repeka.com');
        $user->setUsername($username);
        $encodedPassword = $passwordEncoder->encodePassword($user, $password);
        $user->setPassword($encodedPassword);
        $user->updateRoles($this->getContainer()->get('repository.user_role')->findAll());
        $em->persist($user);
        $em->flush();
    }
}
