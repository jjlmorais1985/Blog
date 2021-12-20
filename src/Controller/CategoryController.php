<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categories;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\never;

class CategoryController extends AbstractController
{

    public function showCategories(EntityManagerInterface $em)
    {
        $categories = $em->getRepository(Categories::class)->findAll();

        if (empty($categories))
            $this->addFlash(
                'warning',
                'Pas de categories disponibles'
            );
        return $this->render('category/index.html.twig', ['categories' => $categories]);
    }

    public function createCategory(EntityManagerInterface $em, Request $request)
    {
        if (empty($request->get('category'))) {
            $this->addFlash(
                'warning',
                "La catégorie n'a pas ete saisi"
            );
            return $this->redirectToRoute("categories");
        }

        try {
            $category = new Categories();
            $category->setCategory($request->get('category'));
            $em->persist($category);
            $em->flush();
            $this->addFlash(
                "success",
                "La catégorie a été ajouté avec succès"
            );
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash(
                "warning",
                "La catégorie existe déja"
            );
        }

        return $this->redirectToRoute("categories");
    }

    public function removeCategory(EntityManagerInterface $em, $categoryId)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash(
               'warning',
               'Accès Refusé - Vous devez etre administrateur pour voir la page demandé'
            );
            return $this->render('admin/index.html.twig');
        }

        $category = $em->getRepository(Categories::class)->find($categoryId);

        if (empty($category)){
            $this->addFlash(
                'warning',
                'Impossible de supprimer la catégorie.'
            );
            return $this->redirectToRoute("categories");
        }

        $em->remove($category);
        $em->flush();
        $this->addFlash(
            'success',
            'Catégorie supprimer'
        );

        return $this->redirectToRoute("categories");
    }
}
