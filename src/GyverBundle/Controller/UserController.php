<?php

namespace GyverBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class UserController
 * @package GyverBundle\Controller\User
 *
 * @Route("/secure")
 */

class UserController extends Controller
{

    /**
     * @param $id
     * @Route("/account", name="user_info")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function readAction()
    {
        //To get the user who is logged
        $user = $this->get('security.token_storage')->getToken()->getUser();

        return $this->render(':User:form_informations.html.twig', array('user' => $user ));
    }


    /**
     * @Route("/user/{id}")
     * @Method("GET")
     * @Template()
     */
    public function showUserAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('GyverBundle:User')->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }


        return array(
            'user' => $user,
        );
    }

}
