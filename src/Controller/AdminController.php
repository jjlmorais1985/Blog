<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    public function spaceAdmin(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    public function showUsers(EntityManagerInterface $em, $orderBy = "ASC")
    {
        $users = $em->getRepository(User::class)->findBy(
            [],
            ['dateInscription' => $orderBy]
        );
        
        if (empty($users))
            $this->addFlash(
                'warning',
                'Pas de Articles disponibles'

            );

        if ($orderBy === 'ASC')
            $orderBy = 'DESC';
        else
            $orderBy = 'ASC';

        return $this->render('admin/users.html.twig', ['users' => $users, 'orderBy' => $orderBy]);
    }
}
