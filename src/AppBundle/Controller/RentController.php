<?php
/* *******************************************
    Rent Controller
    Controller for the Rental page,
    allows users to reserve there regalia
********************************************* */
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Inventory;
use AppBundle\Entity\Reservation;
use AppBundle\Form\InventoryType;

/**
 * rental controller.
 *
 * @Route("/rent")
 */
class RentController extends Controller
{
    /**
     * @Route("/admin", name="rent")
     * @Method("GET")
     */

     //Shows everything in the inventory that is rentable and allows the user to rent an item

    public function showAction()
    {
        $em = $this->getDoctrine()->getManager();

        $sessionUser = $this->getUser();
        $user = $em->getRepository('AppBundle:User')->findOneByUsername($sessionUser->getUsername()); //get the user entity of session user

        $avalStatus = $em->getRepository('AppBundle:Status')->findOneByName('AVAL'); //place whatever you available status name is here
        $avalInv = $em->getRepository('AppBundle:Inventory')->findByItemStatus($avalStatus->getId()); //place whatever your available status name is here

        return $this->render('rent/index.html.twig', array(
            'aval' => $avalInv,
            'msg' => null 
        ));
    }

    //rent an item, mark it as rented, create the reservation and send a confirmation email
    /**
     * @Route("/{id}", name="rent_item")
     * @Method("GET")
     */
    public function rentAction(Inventory $item) 
    {
        if(!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) 
        {
            throw $this->createAccessDeniedException();
        }

        
        $em = $this->getDoctrine()->getManager();
        $sessionUser = $this->getUser();
        $user = $em->getRepository('AppBundle:User')->findOneByUsername($sessionUser->getUsername()); //get the user entity of session user
        //check to make sure user does not have any rented items of the same type
        $types = []; //array of ids of the types of items that the user already has rented
        foreach($user->getReservations()->toArray() as $res) 
        {
            $types[] = $res->getItem()->getItemType()->getId();
        }

        if(in_array($item->getItemType()->getId(), $types)) //user already has rented item of this type
        {
            $avalStatus = $em->getRepository('AppBundle:Status')->findOneByName('AVAL'); //place whatever you available status name is here
            $avalInv = $em->getRepository('AppBundle:Inventory')->findByItemStatus($avalStatus->getId()); //place whatever your available status name is here

             return $this->render('rent/index.html.twig', array(
                'aval' => $avalInv,
                'msg' => 'You already have a reservation for a ' . $item->getItemType()->getName() . '.
                            Only one rental per type of item is allowed.'     
            ));
            
        }
        else //rent the item
        {
            $rent_status = $em->getRepository('AppBundle:Status')->findOneByName('RENTED');  //set the status to rented
            $item->setItemStatus($rent_status);
            $reservation = new Reservation($user, $item);
            $em = $this->getDoctrine()->getManager();
            $em->persist($reservation);
            $em->flush();
            $admin = $this->container->getParameter('app.admin');

            //send confirmation email to user
            $message = \Swift_Message::newInstance()
                ->setSubject('Rental Request Submitted to WU Regalia Closet')
                ->setFrom('wuregalia@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emailsNotifications/rent/user_confirm.txt.twig',
                            array('item' => $item, 'user' => $user, 'duedate' => $reservation->getDueDate()->format('n-j-Y'), 'admin' => $admin)), 'text/html');
                            $this->get('mailer')->send($message);

            return $this->render('rent/rent.html.twig', array(
                'item' => $item
            ));
        }
    }

}
