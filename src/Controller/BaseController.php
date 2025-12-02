<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    protected function requireUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette ressource.');
        }

        return $user;
    }
}
