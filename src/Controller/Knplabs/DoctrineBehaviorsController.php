<?php

namespace Lopi\Controller\Knplabs;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DoctrineBehaviorsController extends AbstractController
{
    /**
     * @Route("/knplabs/doctrine_behaviors", name="knplabs_doctrine_behaviors")
     */
    public function index(): Response
    {
        return $this->render('knplabs/doctrine_behaviors/index.html.twig');
    }
}
