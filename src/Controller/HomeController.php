<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class HomeController
 * @package App\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY') or is_granted('ROLE_DISPATCHER') or is_granted('ROLE_OPERATOR')", statusCode=404, message="Post not found")
 */
class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
