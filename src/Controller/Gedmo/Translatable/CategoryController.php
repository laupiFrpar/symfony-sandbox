<?php

namespace Lopi\Controller\Gedmo\Translatable;

use Gedmo\Translatable\Entity\Translation;
use Lopi\Entity\Category;
use Lopi\Entity\CategoryTranslation;
use Lopi\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    /**
     * @Route("/gedmo/translatable/category", name="gedmo_translatable_category")
     */
    public function index(): Response
    {
        $categories = $this->getDoctrine()->getManager()->getRepository(Category::class)->findAll();

        return $this->render('gedmo/translatable/category/index.html.twig',[
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/gedmo/translatable/category/new", name="gedmo_translatable_category_new")
     */
    public function newCategory(Request $request, Session $session): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'A new category is saved');

            return $this->redirectToRoute('gedmo_translatable_category');
        }

        return $this->render('gedmo/translatable/category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/gedmo/translatable/category/{id}/edit", name="gedmo_translatable_category_edit")
     */
    public function edit(int $id, Request $request, Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category does not exist');

            return $this->redirectToRoute('gedmo_translatable_category');
        }

        $availableLocales = ['en_US' => 'English', 'fr_FR' => 'French', 'de_De' => 'Deustch'];
        $unavailableLocales = array_map(
            function ($translation) {
                return $translation->getLocale();
            },
            $category->getTranslations()->toArray()
        );


        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'A new category is updated');

            return $this->redirectToRoute('gedmo_translatable_category');
        }

        return $this->render('gedmo/translatable/category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'availableLocales' => array_filter($availableLocales, function ($key) use ($unavailableLocales) {
                return !in_array($key, $unavailableLocales);
            }, ARRAY_FILTER_USE_KEY),
        ]);
    }

    /**
     * @Route("/gedmo/translatable/category/remove/{id}", name="gedmo_translatable_category_remove")
     */
    public function remove(int $id, Session $session): RedirectResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        $category = $entityManager->getRepository(Category::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category does not exist');
        } else {
            $entityManager->remove($category);
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The category is removed');
        }

        return $this->redirectToRoute('gedmo_translatable_category');
    }

    /**
     * @Route("gedmo/translatable/category/{id}/translate/new", name="gedmo_translatable_category_translate_new")
     */
    public function translateNew(int $id, Request $request, Session $session): Response
    {
        // It is too complex to remove a translation
        // https://github.com/doctrine-extensions/DoctrineExtensions/issues/2192
        $session->getFlashBag()->add('warning', 'It is not beautiful to create a translation because we store row by row in database for each field of Category.<br/>If we add a new field in Category, the translation is not automatic. We must update the translation in the code source.<br/>See <a class="alert-link" href="https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#personal-translations">https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/translatable.md#personal-translations</a>');

        $entityManager = $this->getDoctrine()->getManager();
        $locale = $request->query->get('locale');
        $category = $entityManager->getRepository(Category::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category does not exist');

            return $this->redirectToRoute('gedmo_translatable_category');
        }

        $translatedCategory = new Category();
        $form = $this->createForm(CategoryType::class, $translatedCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->addTranslation(new CategoryTranslation($locale, 'title', $translatedCategory->getTitle()));
            $category->addTranslation(new CategoryTranslation($locale, 'description', $translatedCategory->getDescription()));
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The translation is saved');

            return $this->redirectToRoute('gedmo_translatable_category_edit', [
                'id' => $category->getId(),
            ]);
        }

        return $this->render('gedmo/translatable/category/translate.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("gedmo/translatable/category/{id}/translate/{locale}/edit", name="gedmo_translatable_category_translate_edit")
     */
    public function translateEdit(int $id, string $locale,Request $request, Session $session): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->findOneById($id);

        if (!$category) {
            $session->getFlashBag()->add('danger', 'The category does not exist');

            return $this->redirectToRoute('gedmo_translatable_category');
        }

        $category->setTranslatableLocale($locale);
        $entityManager->refresh($category);

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $session->getFlashBag()->add('success', 'The translation is saved');

            return $this->redirectToRoute('gedmo_translatable_category_edit', [
                'id' => $category->getId(),
            ]);
        }

        return $this->render('gedmo/translatable/category/translate.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/gedmo/translatable/category/{id}/translate/{locale}/remove", name="gedmo_translatable_category_translate_remove")
     */
    public function translateRemove(int $id, string $locale, Session $session)
    {
        // It is too complex to remove a translation
        // https://github.com/doctrine-extensions/DoctrineExtensions/issues/2192
        $session->getFlashBag()->add('danger', 'It is too complex to remove a translation, See <a class="alert-link" href="https://github.com/doctrine-extensions/DoctrineExtensions/issues/2192">https://github.com/doctrine-extensions/DoctrineExtensions/issues/2192</a>');

        return $this->redirectToRoute('gedmo_translatable_category_edit', ['id' => $id]);
    }
}
