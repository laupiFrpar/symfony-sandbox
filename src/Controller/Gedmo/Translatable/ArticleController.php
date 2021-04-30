<?php

namespace Lopi\Controller\Gedmo\Translatable;

use Gedmo\Translatable\Entity\Translation;
use Lopi\Entity\Article;
use Lopi\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/gedmo/translatable/article", name="gedmo_translatable_article")
     */
    public function index(): Response
    {
        $articles = $this->getDoctrine()->getManager()->getRepository(Article::class)->findAll();

        return $this->render('gedmo/translatable/article/index.html.twig',[
            'articles' => $articles,
        ]);
    }

    /**
     * @Route("/gedmo/translatable/article/new", name="gedmo_translatable_article_new")
     */
    public function newCategory(Request $request, Session $session): Response
    {
        $article = new Article();

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($article);
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'A new article is saved');

            return $this->redirectToRoute('gedmo_translatable_article');
        }

        return $this->render('gedmo/translatable/article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/gedmo/translatable/article/{id}/edit", name="gedmo_translatable_article_edit")
     */
    public function edit(int $id, Request $request, Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->findOneById($id);

        if (!$article) {
            $session->getFlashBag()->add('danger', 'The article does not exist');

            return $this->redirectToRoute('gedmo_translatable_article');
        }

        $defaultAvailableLocales = ['en_US' => 'English', 'fr_FR' => 'French', 'de_De' => 'Deustch'];
        $translations = $entityManager->getRepository(Translation::class)->findTranslations($article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'A new article is updated');

            return $this->redirectToRoute('gedmo_translatable_article');
        }

        return $this->render('gedmo/translatable/article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
            'translations' => $translations,
            'availableLocales' => array_diff_key($defaultAvailableLocales, $translations),
        ]);
    }

    /**
     * @Route("/gedmo/translatable/article/remove/{id}", name="gedmo_translatable_article_remove")
     */
    public function remove(int $id, Session $session): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        $article = $entityManager->getRepository(Article::class)->findOneById($id);

        if (!$article) {
            $session->getFlashBag()->add('danger', 'The article does not exist');
        } else {
            $entityManager->remove($article);
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The article is removed');
        }

        return $this->redirectToRoute('gedmo_translatable_article');
    }

    /**
     * @Route("gedmo/translatable/article/{id}/translate/new", name="gedmo_translatable_article_translate_new")
     */
    public function translateNew(int $id, Request $request, Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $locale = $request->query->get('locale');
        $article = $entityManager->getRepository(Article::class)->findOneById($id);
        $article->setTranslatableLocale($locale);
        $entityManager->refresh($article);

        if (!$article) {
            $session->getFlashBag()->add('danger', 'The article does not exist');

            return $this->redirectToRoute('gedmo_translatable_article');
        }

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article->setTranslatableLocale($locale);
            $entityManager->persist($article);
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The translation is saved');

            return $this->redirectToRoute('gedmo_translatable_article_edit', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('gedmo/translatable/article/translate.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("gedmo/translatable/article/{id}/translate/{locale}/edit", name="gedmo_translatable_article_translate_edit")
     */
    public function translateEdit(int $id, string $locale,Request $request, Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $article = $entityManager->getRepository(Article::class)->findOneById($id);

        if (!$article) {
            $session->getFlashBag()->add('danger', 'The article does not exist');

            return $this->redirectToRoute('gedmo_translatable_article');
        }

        $article->setTranslatableLocale($locale);
        $entityManager->refresh($article);

        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The translation is saved');

            return $this->redirectToRoute('gedmo_translatable_article_edit', [
                'id' => $article->getId(),
            ]);
        }

        return $this->render('gedmo/translatable/article/translate.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/gedmo/translatable/article/{id}/translate/{locale}/remove", name="gedmo_translatable_article_translate_remove")
     */
    public function translateRemove(int $id, string $locale, Session $session)
    {
        // It is too complex to remove a translation
        // https://github.com/doctrine-extensions/DoctrineExtensions/issues/2192
        $session->getFlashBag()->add('danger', 'It is too complex to remove a translation, See <a class="alert-link" href="https://github.com/doctrine-extensions/DoctrineExtensions/issues/2192">https://github.com/doctrine-extensions/DoctrineExtensions/issues/2192</a>');

        return $this->redirectToRoute('gedmo_translatable_article_edit', ['id' => $id]);
    }
}
