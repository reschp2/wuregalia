<?php
/* --------------------------------------------------
    LoginController.php
    Handles sign ins, authenticates users via LDAP
    Author: Noah Weber
------------------------------------------------------- */

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Form\LoginType;
use Symfony\Component\Ldap\Exception\ConnectionException;

class LoginController extends Controller
{

    /**
     * Login controller.
     *
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authUtils = $this->get('security.authentication_utils');
        // get the login error if there is one
        $error = $authUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authUtils->getLastUsername();

        $loginMessage = "Must be signed in!";

        //getting the entity manganer as $em
        $em = $this->getDoctrine()->getManager();

        $avalStatus = $em->getRepository('AppBundle:Status')->findOneByName('AVAL'); //place whatever you available status name is here
        $inventories = $em->getRepository('AppBundle:Inventory')->findByItemStatus($avalStatus->getId());

        return $this->render('default/index.html.twig', array(
            'inventories' => $inventories,
            'loginMessage' => $loginMessage
        ));
    }

}
