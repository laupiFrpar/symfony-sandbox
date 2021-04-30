<?php

namespace Lopi\Controller\Knplabs\DoctrineBehaviors\Translatable;

use Lopi\Entity\Category2;
use Lopi\Entity\Category2Translation;
use Lopi\Form\Category2TranslationType;
use Lopi\Form\Category2Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/knplabs/doctrine_behaviors/translatable/category", name="knplabs_doctrine_behaviors_translatable_category")
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()->getManager()->getRepository(Category2::class)->findAll();

        return $this->render('knplabs/doctrine_behaviors/translatable/category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/knplabl/doctrine_behaviors/translatable/category/{id}/edit", name="knplabs_doctrine_behaviors_translatable_category_edit")
     */
    public function edit(int $id, Request $request, Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category2::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category does not exist');

            return $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category');
        }

        $availableLocales = ['en_US' => 'English', 'fr_FR' => 'French', 'de_De' => 'Deustch'];
        $unavailableLocales = array_map(
            function ($translation) {
                return $translation->getLocale();
            },
            $category->getTranslations()->toArray()
        );

        $form = $this->createForm(Category2Type::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'A new category is updated');

            return $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category');
        }

        return $this->render('knplabs/doctrine_behaviors/translatable/category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'availableLocales' => array_diff_key($availableLocales, $unavailableLocales),
        ]);
    }

    /**
     * @Route("/knplabs/doctrine_behaviors/translatable/category/new", name="knplabs_doctrine_behaviors_translatable_category_new")
     */
    public function new(Request $request, Session $session): Response
    {
        $category = new Category2();

        $form = $this->createForm(Category2Type::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->translate('en_US')->setName($form->get('name')->getData());
            $category->translate('en_US')->setDescription($form->get('description')->getData());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);

            // In order to persist new translations, call mergeNewTranslations method, before flush
            $category->mergeNewTranslations();

            $entityManager->flush();
            $session->getFlashBag()->add('success', 'A new category is saved');

            return $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category');
        }

        return $this->render('knplabs/doctrine_behaviors/translatable/category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/knplabs/doctrine_behaviors/translatable/category/remove/{id}", name="knplabs_doctrine_behaviors_translatable_category_remove")
     */
    public function remove(int $id, Session $session): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        $category = $entityManager->getRepository(Category2::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category does not exist');
        } else {
            $entityManager->remove($category);
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The category is removed');
        }

        return $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category');
    }

    /**
     * @Route("knplabs/doctrine_behaviors/translatable/category/{id}/translate/new", name="knplabs_doctrine_behaviors_translatable_category_translate_new")
     */
    public function translateNew(int $id, Request $request, Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $locale = $request->query->get('locale');
        $category = $entityManager->getRepository(Category2::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category does not exist');

            return $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category');
        }

        $categoryTranslation = new Category2Translation();
        $categoryTranslation->setLocale($locale);
        $form = $this->createForm(Category2TranslationType::class, $categoryTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->addTranslation($categoryTranslation);

            // In order to persist new translations, call mergeNewTranslations method, before flush
            $category->mergeNewTranslations();

            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The translation is saved');

            return $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category_edit', [
                'id' => $category->getId(),
            ]);
        }

        return $this->render('knplabs/doctrine_behaviors/translatable/category/translate.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/knplabs/doctrine_behaviors/translatable/category/{id}/translate/{locale}/edit", name="knplabs_doctrine_behaviors_translatable_category_translate_edit")
     */
    public function translateEdit(int $id, string $locale, Request $request, Session $session)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category2::class)->findOneById($id);
        $redirectResponse = $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category_edit', ['id' => $id]);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category is not found');

            return $redirectResponse;
        }

        $categoryTranslation = $entityManager->getRepository(Category2Translation::class)->findOneByCategoryAndLocale($category, $locale);

        if (!$categoryTranslation) {
            $session->getFlashBag()->add('danger', 'The category translation '.$locale.' is not found');

            return $redirectResponse;
        }

        $form = $this->createForm(Category2TranslationType::class, $categoryTranslation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The translation is saved');

            return $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category_edit', [
                'id' => $category->getId(),
            ]);
        }

        return $this->render('knplabs/doctrine_behaviors/translatable/category/translate.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/knplabs/doctrine_behaviors/translatable/category/{id}/translate/{locale}/remove", name="knplabs_doctrine_behaviors_translatable_category_translate_remove")
     */
    public function translateRemove(int $id, string $locale, Session $session): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category2::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category is not found');

            return $response;
        }

        $categoryTranslation = $entityManager->getRepository(Category2Translation::class)->findOneByCategoryAndLocale($category, $locale);
        $response = $this->redirectToRoute('knplabs_doctrine_behaviors_translatable_category_edit', ['id' => $id]);

        if (!$categoryTranslation) {
            $session->getFlashBag()->add('danger', 'The category translation '.$locale.' is not found');

            return $response;
        }

        $entityManager->remove($categoryTranslation);
        $entityManager->flush();

        $session->getFlashBag()->add('success', 'The category translation '.$locale.' is removed');

        return $response;
    }
}
