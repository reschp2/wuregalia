<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\School;
use AppBundle\Form\SchoolType;

/**
 * School controller.
 *
 * @Route("/admin/school")
 */
class SchoolController extends Controller
{
    /**
     * Lists all School entities.
     *
     * @Route("/", name="school_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $schools = $em->getRepository('AppBundle:School')->findAll();

        return $this->render('school/index.html.twig', array(
            'schools' => $schools,
        ));
    }

    /**
     * Creates a new School entity.
     *
     * @Route("/new", name="school_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $school = new School();
        $form = $this->createForm('AppBundle\Form\SchoolType', $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($school);
            $em->flush();

            return $this->redirectToRoute('school_show', array('id' => $school->getId()));
        }

        return $this->render('school/new.html.twig', array(
            'school' => $school,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a School entity.
     *
     * @Route("/{id}", name="school_show")
     * @Method("GET")
     */
    public function showAction(School $school)
    {
        $deleteForm = $this->createDeleteForm($school);

        return $this->render('school/show.html.twig', array(
            'school' => $school,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing School entity.
     *
     * @Route("/{id}/edit", name="school_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, School $school)
    {
        $deleteForm = $this->createDeleteForm($school);
        $editForm = $this->createForm('AppBundle\Form\SchoolType', $school);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($school);
            $em->flush();

            return $this->redirectToRoute('school_edit', array('id' => $school->getId()));
        }

        return $this->render('school/edit.html.twig', array(
            'school' => $school,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a School entity.
     *
     * @Route("/{id}", name="school_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, School $school)
    {
        $form = $this->createDeleteForm($school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($school);
            $em->flush();
        }

        return $this->redirectToRoute('school_index');
    }

    /**
     * Creates a form to delete a School entity.
     *
     * @param School $school The School entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(School $school)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('school_delete', array('id' => $school->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
