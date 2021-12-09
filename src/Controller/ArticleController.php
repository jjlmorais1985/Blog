<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categories;
use App\Form\ArticleType;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;


class ArticleController extends AbstractController
{
    public function showArticles(EntityManagerInterface $em)
    {
        $articles = $em->getRepository(Article::class)->findAll();

        if (empty($articles))
            $this->addFlash(
                'warning',
                'Pas de Articles disponibles'

            );
        return $this->render('article/index.html.twig', ['articles' => $articles]);
    }


    public function createArticle(EntityManagerInterface $em, Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
//            Upload Image
            $image = $form->get('image')->getData();
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $article->setImage($newFilename);
            }
//            Upload Image
            $categories = $form->get('categories');
            foreach ($categories as $cat) {
                var_dump($cat);
                $article->addCategory($cat);
            }
            $article->setDate(new \DateTime('now'));
            $article->setDateUpdate(new \DateTime('now'));
            $em->persist($article);
            $em->flush();
            $this->addFlash(
                'success',
                'Articles Créé.'
            );
            return $this->redirectToRoute('articles');
        }
        return $this->render('article/formArticle.html.twig', ['form' => $form->createView()]);
    }


    public function removeArticle(EntityManagerInterface $em, $articleId)
    {
        $article = $em->getRepository(Article::class)->find($articleId);

        if (empty($article)) {
            $this->addFlash(
                "warning",
                "Impossible de supprimer l'article $articleId."
            );
            return $this->redirectToRoute('articles');
        }
        $em->remove($article);
        $em->flush();
        $title = $article->getTitle();
        $this->addFlash(
            "success",
            "Article $title a été supprimé."
        );
        return $this->redirectToRoute('articles');
    }


    public function updateArticle(EntityManagerInterface $em, Request $request, $articleId=0)
    {
        $article = $em->getRepository(Article::class)->find($articleId);

        if (empty($article)){
            $this->addFlash(
                "warning",
                "Mise à jour de l'article d'id \"$articleId\" impossible."
            );
            $this->redirectToRoute('articles');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){

//            Upload Image
            $image = $form->get('image')->getData();
            if ($image) {
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $image->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $article->setImage($newFilename);
            }
//            Upload Image

            $article->setDateUpdate(new \DateTime('now'));
            $em->flush();
            $this->addFlash(
                'success',
                "L'Article' «  » a été modifiée avec succès"
            );
            return $this->redirectToRoute('articles', ['articleId' => $articleId]);
        }

//        return $this->render('article/formEditArticle.html.twig', ['article' => $article]);
        return $this->render('article/formArticle.html.twig', ['form' => $form->createView()]);
    }


    public function publishArticle(EntityManagerInterface $em, $articleId)
    {
        $article = $em->getRepository(Article::class)->find($articleId);

        if (empty($article)){
            $this->addFlash(
                "warning",
                "Impossible de publier l'article $articleId"
            );
            $this->redirectToRoute('articles');
        }

        $article->setPublish(!$article->getPublish());
        $article->setDateUpdate(new \DateTime('now'));
        $em->flush();

        if ($article->getPublish()) {
            $this->addFlash(
                "success",
                "L'article $articleId a été publié"
            );
        } else {
            $this->addFlash(
            "success",
            "L'article $articleId n'est plus publié"
            );
        }
        return $this->redirectToRoute('articles');

    }
}
