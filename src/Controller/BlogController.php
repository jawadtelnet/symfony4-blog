<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Form\CommentairesType;
use App\Form\CommentType;

class BlogController extends AbstractController
{
    public function index()
    {
        $articles = $this->getDoctrine()->getRepository(Article::class)->findBy(
            ['isPublished' => true],
            ['publicationDate' => 'desc']
        );

        return $this->render('blog/index.html.twig', ['articles' => $articles]);
    }

    public function add(Request $request)
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());
            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'L\'article a bien été enregistrer.');


            return $this->redirectToRoute('homepage');
        }

        return $this->render('blog/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function show(Article $article, Request $request)
    {

        // Nous créons l'instance de "Commentaires"
        $commentaire = new Comment();

        // Nous créons le formulaire en utilisant "CommentairesType" et on lui passe l'instance
        $form = $this->createForm(CommentType::class, $commentaire);

        // Nous récupérons les données
        $form->handleRequest($request);

        // Nous vérifions si le formulaire a été soumis et si les données sont valides
        if ($form->isSubmitted() && $form->isValid()) {
            // Hydrate notre commentaire avec l'article
            $commentaire->setArticle($article)
                ->setUser($this->getUser())
                ->setCreatedAt(new \DateTime('now'));

            $doctrine = $this->getDoctrine()->getManager();

            // On hydrate notre instance $commentaire
            $doctrine->persist($commentaire);

            // On écrit en base de données
            $doctrine->flush();
            return $this->redirectToRoute('article_show', ['id' => $article->getId()]);
        }


        return $this->render('blog/show.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    public function edit(Article $article, Request $request)
    {
        $oldPicture = $article->getPicture();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setLastUpdateDate(new \DateTime());

            if ($article->getIsPublished()) {
                $article->setPublicationDate(new \DateTime());
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($article);
            $em->flush();
            $this->addFlash('success', 'L\'article a bien été modifier.');
            return $this->redirectToRoute('homepage');
        }

        return $this->render('blog/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView()
        ]);
    }

    public function remove($id)
    {
        $this->addFlash('success', 'Delete article: ' . $id);
        return $this->redirectToRoute('homepage');
    }
}
