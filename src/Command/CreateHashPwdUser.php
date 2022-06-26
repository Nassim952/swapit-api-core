<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateHashPwdUser extends Command
{

    private $passwordHasher;
    private $em;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em)
    {
        $this->passwordHasher = $passwordHasher;
        $this->em = $em;
        parent::__construct();
    }

    public function createUser()
    {
        $user = new User();
        $user->setUsername('jwt');
        $user->setEmail('jwt@gmail.com');
        $plaintextPassword = 'password';

        // hash the password (based on the security.yaml config for the $user class)
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        // and save user in db
        $this->em->persist($user);
        $this->em->flush();
    }

    protected function configure()
    {
        // On set le nom de la commande
        $this->setName('app:createuser');

        // On set la description
        $this->setDescription("Créer un user avec un mdp hashé");

        // On set l'aide
        $this->setHelp("créee un user avec un mdp hashé");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('memory_limit', '1024M');
        $this->createUser();
        $output->write('User créee avec succès !');
        return Command::SUCCESS;
    }
}
