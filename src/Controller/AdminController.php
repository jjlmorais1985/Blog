<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AdminController extends AbstractController
{
    public function spaceAdmin(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    public function showUsers(EntityManagerInterface $em, $orderBy = "ASC")
    {
        
        // $this->denyAccessUnlessGranted('ROLE_ADMIN');
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash(
               'warning',
               'Accès Refusé - Vous devez etre administrateur pour voir la page demandé'
            );
            return $this->render('admin/index.html.twig');
        }
        

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

    public function delUsers(EntityManagerInterface $em, $id)
    {
        $user = $em->getRepository(User::class)->find($id);
        
        if (!empty($user)) {
            $username = $user->getUsername();
            $em->remove($user);
            $em->flush();
            $this->addFlash(
               "success",
               "Utilisateur \" $id : $username\" supprimé"
            );

        } else {
            $this->addFlash(
            'warning',
            "Impossible de supprimer l'utilisareur"
            );
        }
        return $this->redirectToRoute('showUsers', ["orderBy" => "ASC"]);
    }

    public function setAdmin(EntityManagerInterface $em, $id) {

        $user = $em->getRepository(User::class)->find($id);

        if (in_array("ROLE_ADMIN", $user->getRoles())){
            $user->setRoles(["ROLE_USER"]);
            $this->addFlash(
               'success',
               'Permissons admin enlevé'
            );
        } else {
            $this->addFlash(
                'success',
                'Permissons admin ajouté'
             );
            $user->setRoles(["ROLE_USER", "ROLE_ADMIN"]);
        }
        $em->flush($user);

        return $this->redirectToRoute("showUsers", ["orderBy" => "ASC"]); 
    }
}
