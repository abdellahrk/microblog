<?php

namespace App\Command;

use App\Domain\User\Event\UserRegistered;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:add-admin-user',
    description: 'Add a new admin user to the app.',
)]
class AddAdminUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly SluggerInterface $slugger,
        private EventDispatcherInterface $eventDispatcher,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');
        
        $emailPrompt = new Question('Enter your email: ');
        $email = $this->getHelper('question')->ask($input, $output, $emailPrompt);
        $user = $this->userRepository->findOneBy(['email' => $email]);
        if ($user instanceof User) {
            $io->warning('User with this email already exists.');
            return Command::SUCCESS;
        }

        $userNamePrompt = new Question('Enter your username: ');
        $username = $this->getHelper('question')->ask($input, $output, $userNamePrompt);
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if ($user instanceof User) {
            $io->warning('User with this username already exists.');
            return Command::SUCCESS;
        }

        $fullNamePrompt = new Question('Enter your full name: ');
        $fullName = $this->getHelper('question')->ask($input, $output, $fullNamePrompt);
        $user = $this->userRepository->findOneBy(['username' => $fullName]);
        if ($user instanceof User) {
            $io->warning('User with this fullName already exists.');
            return Command::SUCCESS;
        }

        $passwordPrompt = new Question('Enter your password: ');
        $password = $this->getHelper('question')->ask($input, $output, $passwordPrompt);

        $user = new User();
        $user
            ->setFullName($fullName)
            ->setUsername($username)
            ->setEmail($email)
            ->setPassword($this->userPasswordHasher->hashPassword($user, $password))
            ->setRoles(['ROLE_ADMIN', 'ROLE_USER', 'ROLE_AUTHOR'])
            ->setSlug(strtolower($this->slugger->slug($fullName)))
            ->setIsActive(true)
        ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('New admin user has been created. You can log in with email or username');
        
        $this->eventDispatcher->dispatch(new UserRegistered($user->getId()));

        return Command::SUCCESS;
    }
}
