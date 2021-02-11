<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManager = $entityManagerInterface;

        parent::__construct();
    }

    protected function configure()
    {
        $this->addOption('username', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->hasOption('username') || is_null($input->getOption('username'))) {
            $output->writeln('<fg=red>Do not forget to specify the username.</>');
            return Command::FAILURE;
        }

        $user = (new User())
            ->setUsername($username = $input->getOption('username'))
            ->setApiToken($token = hash('sha256', sprintf('%s:%s', $username, (string)time())));

        $this->entityManager->persist($user);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $output->writeln(sprintf('<fg=red>%s</>%s%s', (new ReflectionClass($e))->getShortName(), PHP_EOL, $e->getMessage()));
            return Command::FAILURE;
        }

        $output->writeln(sprintf('Hello <fg=green>%s</>, your token: %s', $user->getUsername(), $token));

        return Command::SUCCESS;
    }
}
