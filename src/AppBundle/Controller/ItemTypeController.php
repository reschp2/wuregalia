<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\ItemType;
use AppBundle\Form\ItemTypeType;

/**
 * ItemType controller.
 *
 * @Route("/admin/itemtype")
 */
class ItemTypeController extends Controller
{
    /**
     * Lists all ItemType entities.
     *
     * @Route("/", name="itemtype_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $itemTypes = $em->getRepository('AppBundle:ItemType')->findAll();

        return $this->render('itemtype/index.html.twig', array(
            'itemTypes' => $itemTypes,
        ));
    }

    /**
     * Creates a new ItemType entity.
     *
     * @Route("/new", name="itemtype_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $itemType = new ItemType();
        $form = $this->createForm('AppBundle\Form\ItemTypeType', $itemType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($itemType);
            $em->flush();

            return $this->redirectToRoute('itemtype_show', array('id' => $itemType->getId()));
        }

        return $this->render('itemtype/new.html.twig', array(
            'itemType' => $itemType,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a ItemType entity.
     *
     * @Route("/{id}", name="itemtype_show")
     * @Method("GET")
     */
    public function showAction(ItemType $itemType)
    {
        $deleteForm = $this->createDeleteForm($itemType);

        return $this->render('itemtype/show.html.twig', array(
            'itemType' => $itemType,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing ItemType entity.
     *
     * @Route("/{id}/edit", name="itemtype_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ItemType $itemType)
    {
        $deleteForm = $this->createDeleteForm($itemType);
        $editForm = $this->createForm('AppBundle\Form\ItemTypeType', $itemType);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($itemType);
            $em->flush();

            return $this->redirectToRoute('itemtype_edit', array('id' => $itemType->getId()));
        }

        return $this->render('itemtype/edit.html.twig', array(
            'itemType' => $itemType,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a ItemType entity.
     *
     * @Route("/{id}", name="itemtype_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, ItemType $itemType)
    {
        $form = $this->createDeleteForm($itemType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($itemType);
            $em->flush();
        }

        return $this->redirectToRoute('itemtype_index');
    }

    /**
     * Creates a form to delete a ItemType entity.
     *
     * @param ItemType $itemType The ItemType entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ItemType $itemType)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('itemtype_delete', array('id' => $itemType->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
