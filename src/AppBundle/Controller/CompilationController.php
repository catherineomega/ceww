<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AppBundle\Entity\Compilation;
use AppBundle\Entity\Contribution;
use AppBundle\Form\CompilationType;
use AppBundle\Form\ContributionType;

/**
 * Compilation controller.
 *
 * @Route("/compilation")
 */
class CompilationController extends Controller
{
    /**
     * Lists all Compilation entities.
     *
     * @Route("/", name="compilation_index")
     * @Method("GET")
     * @Template()
	 * @param Request $request
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $qb->select('e')->from(Compilation::class, 'e')->orderBy('e.sortableTitle', 'ASC');
        $query = $qb->getQuery();
        $paginator = $this->get('knp_paginator');
        $compilations = $paginator->paginate($query, $request->query->getint('page', 1), $this->getParameter('page_size'));

        return array(
            'compilations' => $compilations,
        );
    }

    /**
     * Creates a new Compilation entity.
     *
     * @Route("/new", name="compilation_new")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
     */
    public function newAction(Request $request)
    {
        if( ! $this->isGranted('ROLE_CONTENT_EDITOR')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $compilation = new Compilation();
        $form = $this->createForm(CompilationType::class, $compilation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach($compilation->getContributions() as $contribution) {
                $contribution->setPublication($compilation);
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($compilation);
            $em->flush();

            $this->addFlash('success', 'The new collection was created.');
            return $this->redirectToRoute('compilation_show', array('id' => $compilation->getId()));
        }

        return array(
            'compilation' => $compilation,
            'form' => $form->createView(),
        );
    }

    /**
     * Finds and displays a Compilation entity.
     *
     * @Route("/{id}", name="compilation_show")
     * @Method("GET")
     * @Template()
	 * @param Compilation $compilation
     */
    public function showAction(Compilation $compilation)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $em->getRepository(Compilation::class);

        return array(
            'compilation' => $compilation,
            'next' => $repo->next($compilation),
            'previous' => $repo->previous($compilation),
        );
    }

    /**
     * Displays a form to edit an existing Compilation entity.
     *
     * @Route("/{id}/edit", name="compilation_edit")
     * @Method({"GET", "POST"})
     * @Template()
	 * @param Request $request
	 * @param Compilation $compilation
     */
    public function editAction(Request $request, Compilation $compilation)
    {
        if( ! $this->isGranted('ROLE_CONTENT_EDITOR')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $editForm = $this->createForm(CompilationType::class, $compilation);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            foreach($compilation->getContributions() as $contribution) {
                $contribution->setPublication($compilation);
            }
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The collection has been updated.');
            return $this->redirectToRoute('compilation_show', array('id' => $compilation->getId()));
        }

        return array(
            'compilation' => $compilation,
            'edit_form' => $editForm->createView(),
        );
    }

    /**
     * Deletes a Compilation entity.
     *
     * @Route("/{id}/delete", name="compilation_delete")
     * @Method("GET")
	 * @param Request $request
	 * @param Compilation $compilation
     */
    public function deleteAction(Request $request, Compilation $compilation)
    {
        if( ! $this->isGranted('ROLE_CONTENT_ADMIN')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($compilation);
        $em->flush();
        $this->addFlash('success', 'The compilation was deleted.');

        return $this->redirectToRoute('compilation_index');
    }

        /**
     * Creates a new Compilation contribution entity.
     *
     * @Route("/{id}/contributions/new", name="compilation_new_contribution")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @param Compilation $compilation
     */
    public function newContribution(Request $request, Compilation $compilation) {
        if (!$this->isGranted('ROLE_CONTENT_EDITOR')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $contribution = new Contribution();

        $form = $this->createForm(ContributionType::class, $contribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contribution->setPublication($compilation);
            $em = $this->getDoctrine()->getManager();
            $em->persist($contribution);
            $em->flush();

            $this->addFlash('success', 'The new contribution was created.');
            return $this->redirectToRoute('compilation_show_contributions', array('id' => $compilation->getId()));
        }

        return array(
            'compilation' => $compilation,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Show compilation contributions list with edit/delete action items
     * 
     * @Route("/{id}/contributions", name="compilation_show_contributions")
     * @Method("GET")
     * @Template()
     * @param Compilation $compilation
     */
    public function showContributions(Compilation $compilation) {
        if (!$this->isGranted('ROLE_CONTENT_EDITOR')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        return array(
            'compilation' => $compilation,
        );
    }
    
    /**
     * Displays a form to edit an existing compilation Contribution entity.
     *
     * @Route("/contributions/{id}/edit", name="compilation_edit_contributions")
     * @Method({"GET", "POST"})
     * @Template()
     * @param Request $request
     * @param Compilation $compilation
     */
    public function editContribution(Request $request, Contribution $contribution) {
        if (!$this->isGranted('ROLE_CONTENT_EDITOR')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }

        $editForm = $this->createForm(ContributionType::class, $contribution);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            $this->addFlash('success', 'The contribution has been updated.');
            return $this->redirectToRoute('compilation_show_contributions', array('id' => $contribution->getPublicationId()));
        }

        return array(
            'contribution' => $contribution,
            'edit_form' => $editForm->CreateView(),
        );
    }

    /**
     * Deletes a compilation Contribution entity.
     *
     * @Route("/contributions/{id}/delete", name="compilation_delete_contributions")
     * @Method("GET")
     * @param Request $request
     * @param Contribution $contribution
     */
    public function deleteContribution(Request $request, Contribution $contribution) {
        if (!$this->isGranted('ROLE_CONTENT_ADMIN')) {
            $this->addFlash('danger', 'You must login to access this page.');
            return $this->redirect($this->generateUrl('fos_user_security_login'));
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($contribution);
        $em->flush();
        $this->addFlash('success', 'The contribution was deleted.');

        return $this->redirectToRoute('compilation_show_contributions', array('id' => $contribution->getPublicationId()));
    }

}
