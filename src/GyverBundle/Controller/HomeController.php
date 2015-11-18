<?php

namespace GyverBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HomeController
 * @package GyverBundle\Controller
 *
 * @Route("/")
 */
class HomeController extends Controller
{
    /**
     * Return the application home page
     *
     * @Route("/", name="front_homepage")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        return $this->redirectToRoute('user_login', array(), 301);
    }

}
