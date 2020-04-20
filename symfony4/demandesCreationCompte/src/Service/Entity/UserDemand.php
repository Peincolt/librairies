<?php

namespace App\Service\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use App\Entity\UserDemand as UserDemandEntity;
use App\Service\Entity\User as UserHelper;
use App\Entity\User;
use App\Service\Security as SecurityHelper;

class UserDemand
{

    private $userHelper;
    private $entityManagerInterface;
    private $securityHelper;

    public function __construct(EntityManagerInterface $entityManagerInterface,
        SecurityHelper $securityHelper,
        UserHelper $userHelper
    )
    {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->securityHelper = $securityHelper;
        $this->userHelper = $userHelper;
    }

    public function createUserDemand(UserDemandEntity $user)
    {
        $arrayReturn = array();
        $createdAt = new \DateTime();

        if (!$this->securityHelper->isPasswordCorrect($user->getPassword())) {
            $arrayReturn['error_message'] = 'Votre mot de passe doit contenir au moins 8 caractéres dont une lettre majuscule, un chiffre et un caractére spécial';
            $arrayReturn['error_forms']['password'] = 'Votre mot de passe doit contenir au moins 8 caractéres dont une lettre majuscule, un chiffre et un caractére spécial';
            return $arrayReturn;
        }

        $isEmailTaken = $this->userHelper->isFieldTaken('email',$user->getEmail());
        $isUsernameTaken = $this->userHelper->isFieldTaken('username',$user->getUsername());

        if (isset($isEmailTaken['error_message']) || isset($isUsernameTaken['error_message'])) {
            if (isset($isEmailTaken['error_message'])) {
                return $isEmailTaken;
            } else {
                return $isUsernameTaken;
            }
        }

        $user->setCreatedAt($createdAt);

        try {
            $this->entityManagerInterface->persist($user);
            $this->entityManagerInterface->flush();
            return 200;
        } catch (Exception $e) {
            return array('error_message' => 'Une erreur est survenue lors de la sauvegarde de votre demande de compte. Veuillez réessayer plus tard');
        }
    }

    public function transformDemandToAccount(array $ids)
    {
        try {
            foreach($ids as $id) {
                $demand = $this->entityManagerInterface
					->getRepository(UserDemandEntity::class)
					->find($id);
                $user = new User();
                $user->setUsername($demand->getUsername())
                    ->setPassword($this->securityHelper
                        ->hashPassword($user,$demand->getPassword()))
                    ->setEmail($demand->getEmail())
                    ->setRoles(array('ROLE_'.$demand->getRole()));
                $this->entityManagerInterface->persist($user);
                $this->entityManagerInterface->remove($demand);
                $this->entityManagerInterface->flush();
            }
            return 200;
        } catch (Exception $e) {
            $arrayReturn['error_message'] = $e->getMessage();
            $arrayReturn['error_code'] = 404;
            return $arrayReturn;
        }
    }
}
