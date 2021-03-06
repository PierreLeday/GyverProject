<?php

namespace GP\UserBundle\Controller\Admin;

use GP\CoreBundle\Entity\Company;
use GP\CoreBundle\Entity\User;
use GP\UserBundle\Form\Type\Admin\NewCompanyType;
use GP\UserBundle\Form\Type\Admin\AddUserToCompanyType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class Admin Company Controller
 * @package GP\UserBundle\Controller
 *
 * @Route("/secure/admin/company")
 */
class AdminCompanyController extends Controller
{
    /**
     * Create a new company
     *
     * @Route("/new", name="admin_create_company")
     * @Method("GET|POST")
     * @Template("GPUserBundle:Admin/Company:createCompany.html.twig")
     */
    public function createCompanyAction(Request $request)
    {
        $company = new Company();
        $form = $this->createForm(new NewCompanyType(), $company);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($company);
            $em->flush();

            $logger = $this->get('monolog.logger.user_access');
            $logger->alert('[COMPANY_CREATION] ' . $this->getUser()->getEmail() .' have created new company '. $company->getName());

            $this->addFlash('success', 'L\'entreprise '. $company->getName() .' a été correctement crée');
            return $this->redirectToRoute('admin_show_all_company');
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * Returns the list of companies registered in the application
     *
     * @Route("/", name="admin_show_all_company")
     * @Method("GET")
     * @Template("GPUserBundle:Admin/Company:showCompanies.html.twig")
     */
    public function showCompaniesAction(Request $request)
    {
        // Getting all companies
        $em = $this->getDoctrine()->getManager();
        $companies = $em->getRepository('GPCoreBundle:Company')->findAll();

        // Create a pagination
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $companies,
            $request->query->getInt('page', 1),
            $this->container->getParameter( 'knp_paginator.page_range' )
        );

        return array('pagination' => $pagination);
    }

    /**
     * Display full data of a given company
     *
     * @Route("/{id}", name="admin_show_company")
     * @Method("GET")
     * @Template("GPUserBundle:Admin/Company:showCompany.html.twig")
     */
    public function showCompanyAction($id)
    {
        // Searching requested company
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('GPCoreBundle:Company')->find($id);

        // Checking if company exists
        if (!$company) {
            $this->addFlash('error', 'Entreprise introuvable');
            return $this->redirectToRoute('admin_show_all_company');
        }

        $projectRepository = $this->getDoctrine()->getRepository('GPCoreBundle:Project');
        $finishedProject = $projectRepository->countFinishedProjects($company);
        $inProgressProjectNb = $projectRepository->countProjectsInProgress($company);

        return array(
            'company' => $company,
            'finishedProjectNb' => $finishedProject,
            'inProgressProjectNb' => $inProgressProjectNb
        );
    }

    /**
     * Update a given company
     *
     * @Route("/{id}/edit", name="admin_update_company")
     * @Method("GET|POST")
     * @Template("GPUserBundle:Admin/Company:updateCompany.html.twig")
     */
    public function updateCompanyAction(Request $request, $id)
    {
        // Searching requested company
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('GPCoreBundle:Company')->find($id);

        // Checking if company exists
        if (!$company) {
            $this->addFlash('error', 'Entreprise introuvable');
            return $this->redirectToRoute('admin_show_all_company');
        }

        $form = $this->createForm(new NewCompanyType(), $company);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($company);
            $em->flush();

            $this->addFlash('success', 'L\'entreprise '. $company->getName() .' a été correctement mise à jour');
            return $this->redirectToRoute('admin_show_all_company');
        }

        return array(
            'form' => $form->createView(),
            'company' => $company
        );
    }

    /**
     * Delete a given company /!\ This is definitive, no turning back possible
     *
     * This also delete cascade all elements attached to the company:
     * - All Company Roles (Access Role company)
     * - All Projects Categories
     * And Remove company from projects companies list
     *
     * @Route("/{id}/delete", name="admin_delete_company")
     * @Method("DELETE")
     * @Template()
     */
    public function deleteCompanyAction($id)
    {
        // Searching requested company
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('GPCoreBundle:Company')->find($id);

        // Checking if company exists
        if (!$company) {
            $this->addFlash('error', 'Entreprise introuvable');
            return $this->redirectToRoute('admin_show_all_company');
        }

        // Remove Company
        $em = $this->getDoctrine()->getManager();
        $em->remove($company);
        $em->flush();

        $logger = $this->get('monolog.logger.user_access');
        $logger->alert('[COMPANY_DELETION] ' . $this->getUser()->getEmail() .' have deleted company '. $company->getName());

        $this->addFlash('success', 'L\'entreprise '. $company->getName() .' a été correctement supprimée');
        return $this->redirectToRoute('admin_show_all_company');
    }

    /**
     * Add new user to given company
     *
     * @Route("/{id}/add-user", name="admin_add_user_to_company")
     * @Method("GET|POST")
     * @Template("GPUserBundle:Admin/Company:addUserToCompany.html.twig")
     */
    public function addUserToCompanyAction(Request $request, $id)
    {
        // Searching requested company
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository('GPCoreBundle:Company')->find($id);

        // Checking if company exists
        if (!$company) {
            $this->addFlash('error', 'Entreprise introuvable');
            return $this->redirectToRoute('admin_show_all_company');
        }

        $companyUsers = $company->getUsers();
        $form = $this->createForm(new AddUserToCompanyType($company));

        $form->handleRequest($request);
        if ($form->isValid()) {
            $selectedUser = $form->get('users')->getData();
            $selectedRole = $form->get('companyRoles')->getData();

            if ($selectedRole === null) {
                $this->addFlash('error', 'Vous devez specifier un rôle pour le nouvel utilisateur');
                return $this->redirectToRoute('admin_add_user_to_company', array('id' => $id));
            }

            $username = $selectedUser->getFirstName() . ' ' . $selectedUser->getLastName();

            foreach ($companyUsers as $user) {
                if ($user->getId() == $selectedUser->getId()) {
                    $this->addFlash('error', 'L\'utilisateur ' . $username . ' est déja membre de l\'entreprise ' . $company->getName());
                    return $this->redirectToRoute('admin_show_company', array('id' => $company->getId()));
                }
            }

            $selectedUser->addCompany($company);            // Add user to company
            $selectedUser->addAccessRole($selectedRole);    // Give role to user for company

            $em->persist($selectedUser);
            $em->flush();

            $logger = $this->get('monolog.logger.user_access');
            $logger->alert('[COMPANY_USER_ADD] ' . $this->getUser()->getEmail() .' have added ' . $username . ' to company '. $company->getName());

            $this->addFlash('success', 'L\'utilisateur ' . $username . ' a été correctement ajouté à l\'entreprise ' . $company->getName());
            return $this->redirectToRoute('admin_show_company', array('id' => $company->getId()));
        }

        return array(
            'company' => $company,
            'form' => $form->createView()
        );
    }

    /**
     * Remove given user from given company
     *
     * TODO Add js popup confirmation & use only DELETE HTTP verb
     *
     * @Route("/{id}/remove-user/{user_id}", name="admin_remove_user_from_company")
     * @Method("GET|DELETE")
     */
    public function removeUserFromCompanyAction(Request $request, $id, $user_id)
    {
        $em = $this->getDoctrine()->getManager();

        $company = $em->getRepository('GPCoreBundle:Company')->find($id);
        if (!$company) {
            $this->addFlash('error', 'Entreprise introuvable');
            return $this->redirectToRoute('admin_show_all_company');
        }

        $user = $em->getRepository('GPCoreBundle:User')->find($user_id);
        if (!$user) {
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('admin_show_company', array('id' => $id));
        }

        $userExistInCompany = $this->checkUserExistInCompany($user, $company);
        $username = $user->getFirstName() . ' ' . $user->getLastName();
        if (!$userExistInCompany) {
            $this->addFlash('error', 'L\'utilisateur ' . $username . ' n\'est pas membre de l\'entreprise ' . $company->getName());
            return $this->redirectToRoute('admin_show_company', array('id' => $company->getId()));
        }

        $userRoleInCompany = $em->getRepository('GPCoreBundle:AccessRole')->findUserRoleInCompany($company, $user);

        if ($userRoleInCompany) {
            $user->removeAccessRole($userRoleInCompany[0]); // Remove company role from User
        }

        $user->removeCompany($company); // Remove User from company

        $em->persist($user);
        $em->flush();

        $logger = $this->get('monolog.logger.user_access');
        $logger->alert('[COMPANY_USER_DELETE] ' . $this->getUser()->getEmail() .' have removed ' . $username . ' from company: '. $company->getName());

        $this->addFlash('success', 'L\'utilisateur ' . $username . ' a correctement été supprimé de l\'entreprise ' . $company->getName());
        return $this->redirectToRoute('admin_show_company', array('id' => $company->getId()));
    }

    /**
     * Check if user is really a member of the company
     * before removing him from it
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    private function checkUserExistInCompany(User $user, Company $company)
    {
        $userCompanies = $user->getCompany();
        $check = false;

        foreach ($userCompanies as $userCompany) {
            if ($userCompany->getId() == $company->getId()) {
                return $check = true;
            }
        }

        return $check;
    }
}
