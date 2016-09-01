<?php
/* *********************************************
    AccountPageController
    A controller for the user's account page.
    This page gives a summary of the user's
    current rentals, donations, requests.
********************************************** */
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;

/**
 * @Route("/admin/account")
 */
class AccountPageController extends Controller
{
    /**
     * Lists the users reservations
     *
     * @Route("/", name="account_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        if(!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) 
        {
            throw $this->createAccessDeniedException();
        }
        
        $em = $this->getDoctrine()->getManager();
        $sessionUser = $this->getUser();
        $user = $em->getRepository('AppBundle:User')->findOneByUsername($sessionUser->getUsername()); //get the user entity of session user
        $reservations = $user->getReservations();

        return $this->render('account/index.html.twig', array(
            'reservations' => $reservations,
        ));
    }
}
