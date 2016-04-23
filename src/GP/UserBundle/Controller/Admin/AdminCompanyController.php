<?php

namespace GP\UserBundle\Controller\Admin;

use GP\CoreBundle\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class Admin Company Controller
 * @package GP\UserBundle\Controller
 *
 * @Route("/secure/admin/company")
 */
class AdminCompanyController extends Controller
{

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
     * Display full data of a given companies
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
            $this->addFlash('error', 'Compagnie introuvable');
            return $this->redirectToRoute('admin_show_all_company');
        }

        // Get company customer & supplier projects
        $customerProjects = $em->getRepository('GPCoreBundle:Project')->findProject($company, ProjectRepository::CUSTOMER);
        $supplierProjects = $em->getRepository('GPCoreBundle:Project')->findProject($company, ProjectRepository::SUPPLIER);

        return array(
            'company' => $company,
            'customerProjects' => $customerProjects,
            'supplierProjects' => $supplierProjects
        );
    }
}
