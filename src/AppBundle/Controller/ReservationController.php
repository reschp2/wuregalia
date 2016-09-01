<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Reservation;
use AppBundle\Form\ReservationType;

/**
 * Reservation controller.
 *
 * @Route("/admin/res", name="reserve")
 */
class ReservationController extends Controller
{
    /**
     * Lists all Reservation entities.
     *
     * @Route("/", name="reservation_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $reservations = $em->getRepository('AppBundle:Reservation')->findAll();

        return $this->render('reservation/index.html.twig', array(
            'reservations' => $reservations,
        ));
    }

    /**
     * Creates a new Reservation entity.
     *
     * @Route("/new", name="reservation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $reservation = new Reservation();
        $form = $this->createForm('AppBundle\Form\ReservationType', $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('reservation_show', array('id' => $reservation->getId()));
        }

        return $this->render('reservation/new.html.twig', array(
            'reservation' => $reservation,
            'form' => $form->createView(),
        ));
    }

    /**
     * Send an email notification to all users with overdue rentals
     *
     * @Route("/notify", name="reservation_notify")
     */

    public function notifyAction()
    {
       //send confirmation email to user

        $em = $this->getDoctrine()->getManager();
        $reservations = $em->getRepository('AppBundle:Reservation')->findAll();
        foreach($reservations as $reservation)
        {
            if($reservation->isLate())
            {
                $user = $reservation->getUserid();
                $item = $reservation->getItem();
                $admin = $this->container->getParameter('app.admin');
                $message = \Swift_Message::newInstance()
                ->setSubject('Overdue Rental')
                ->setFrom('wuregalia@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emailsNotifications/rent/overdue_notify.txt.twig',
                            array('item' => $item, 'user' => $user, 'admin' => $admin)), 'text/html');
                            $this->get('mailer')->send($message);
                }
        }
        return $this->redirectToRoute('reservation_index');
    }



    /**
     * Displays a form to edit an existing Reservation entity.
     *
     * @Route("/{id}/edit", name="reservation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Reservation $reservation)
    {
        $deleteForm = $this->createDeleteForm($reservation);
        $editForm = $this->createForm('AppBundle\Form\ReservationType', $reservation);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);
            $em->flush();

            return $this->redirectToRoute('reservation_edit', array('id' => $reservation->getId()));
        }

        return $this->render('reservation/edit.html.twig', array(
            'reservation' => $reservation,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Reservation entity and set its item to available
     *
     * @Route("/{id}", name="reservation_delete")
     */
    public function deleteAction(Request $request, Reservation $reservation)
    {
        $em = $this->getDoctrine()->getManager();
        $status = $em->getRepository('AppBundle:Status')->findOneByName('AVAL');  //set the status to AVAL
        $reservation->getItem()->setItemStatus($status);
        $em->remove($reservation);
        $em->flush();

        return $this->redirectToRoute('reservation_index');
    }


    /**
     * Creates a form to delete a Reservation entity.
     *
     * @param Reservation $reservation The Reservation entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Reservation $reservation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('reservation_delete', array('id' => $reservation->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
