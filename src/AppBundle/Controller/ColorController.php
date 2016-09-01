<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Color;
use AppBundle\Form\ColorType;

/**
 * Color controller.
 *
 * @Route("/admin/color")
 */
class ColorController extends Controller
{
    /**
     * Lists all Color entities.
     *
     * @Route("/", name="color_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $colors = $em->getRepository('AppBundle:Color')->findAll();

        return $this->render('color/index.html.twig', array(
            'colors' => $colors,
        ));
    }

    /**
     * Creates a new Color entity.
     *
     * @Route("/new", name="color_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $color = new Color();
        $form = $this->createForm('AppBundle\Form\ColorType', $color);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($color);
            $em->flush();

            return $this->redirectToRoute('color_show', array('id' => $color->getId()));
        }

        return $this->render('color/new.html.twig', array(
            'color' => $color,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Color entity.
     *
     * @Route("/{id}", name="color_show")
     * @Method("GET")
     */
    public function showAction(Color $color)
    {
        $deleteForm = $this->createDeleteForm($color);

        return $this->render('color/show.html.twig', array(
            'color' => $color,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Color entity.
     *
     * @Route("/{id}/edit", name="color_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Color $color)
    {
        $deleteForm = $this->createDeleteForm($color);
        $editForm = $this->createForm('AppBundle\Form\ColorType', $color);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($color);
            $em->flush();

            return $this->redirectToRoute('color_edit', array('id' => $color->getId()));
        }

        return $this->render('color/edit.html.twig', array(
            'color' => $color,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Color entity.
     *
     * @Route("/{id}", name="color_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Color $color)
    {
        $form = $this->createDeleteForm($color);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($color);
            $em->flush();
        }

        return $this->redirectToRoute('color_index');
    }

    /**
     * Creates a form to delete a Color entity.
     *
     * @param Color $color The Color entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Color $color)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('color_delete', array('id' => $color->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
