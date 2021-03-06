<?php

namespace GP\SecurityBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\User\UserInterface;

use FOS\UserBundle\Controller\SecurityController as FOSSecurityController;

/**
 * Security manager for the Gyver Project application.
 * Extends the FOS UserBundle SecurityController
 *
 * @package GP\SecurityBundle\Controller
 */
class SecurityController extends FOSSecurityController
{

    /**
     * Redirect from the website index to the login view
     *
     * @Route("/", name="index")
     * @Method("GET")
     */
    public function indexAction()
    {
        return $this->redirectToRoute("login", array(), 301);
    }

    /**
     * Override of the FOSUserBundle login function.
     * Render the login form with the project template.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/login", name="login")
     * @Method("GET")
     * @Template()
     */
    public function loginAction(Request $request)
    {
        // Checking if user already authenticated
        $user = $this->getUser();
        if (is_object($user) || $user instanceof UserInterface)
            return $this->redirectToRoute('dashboard');

        // Checking csrf token and create one if any detected
        $csrfToken = $this->has('form.csrf_provider')
            ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
            : null;

        // Getting authentication service
        $helper = $this->get('security.authentication_utils');

        // Render login view
        return array(
            'last_username' => $helper->getLastUsername(),
            'error'         => $helper->getLastAuthenticationError(),
            'csrf_token'    => $csrfToken,
        );
    }
}
