<?php

namespace App\DataPersister;
use App\Entity\User;
class UserDataPersister implements ContextAwareDataPersisterInterface {

    public function supports($data, array $context = []) : bool
    {
        return $data instanceof User;
      
    }

    public function persist($data, array $context = []) : bool
    {
        if ($data->getPlainPassword()) {
            $data->setPassword(
                $this->userPasswordEncoder->encodePassword($data, $data->getPlainPassword())
            );
            $data->eraseCredentials();
        }
        $this->entityManager->persist($data);
        $this->entityManager->flush();

    }

    public function remove($data, array $context = []) : bool
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
      
    }
}