<?php

namespace Lopi\Controller;

use Lopi\Entity\Category;
use Lopi\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class GedmoController extends AbstractController
{
    /**
     * @Route("/gedmo", name="gedmo")
     */
    public function index(): Response
    {
        return $this->render('gedmo/index.html.twig');
    }
}
