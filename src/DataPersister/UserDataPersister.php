<?php

namespace App\DataPersister;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;

class UserDataPersister implements ContextAwareDataPersisterInterface {

    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
    }

    public function supports($data, array $context = []) : bool
    {
        return $data instanceof User;
    }

    public function persist($data, array $context = [])
    {
        if ($data->getPassword() && !preg_match('/^\$2y/', $data->getPassword())) {
            $data->setPassword($this->passwordHasher->hashPassword($data, $data->getPassword()));
            $data->eraseCredentials();
        }
        
        if($user=$this->security->getUser()){
            if(in_array('ROLE_ADMIN', $data->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles())){
                $data->setRoles(['ROLE_USER']);
            }
        } else {
            $data->setRoles(['ROLE_USER']);
        }
        

        $this->entityManager->persist($data);
        $this->entityManager->flush();

    }

    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }
}