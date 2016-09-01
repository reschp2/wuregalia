<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Inventory;
use AppBundle\Entity\Reservation;
use AppBundle\Form\InventoryType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

 class DefaultController extends Controller
 {
     /**
      * @Route("/", name="homepage")
      */
     public function indexAction(Request $request)
     {
         //getting the entity manganer as $em
        $em = $this->getDoctrine()->getManager();

        $avalStatus = $em->getRepository('AppBundle:Status')->findOneByName('AVAL'); //place whatever you available status name is here
        $inventories = $em->getRepository('AppBundle:Inventory')->findByItemStatus($avalStatus->getId());

        $loginMessage = null;

        return $this->render('default/index.html.twig', array(
            'inventories' => $inventories,
            'loginMessage' => $loginMessage
        ));
     }

    /**
     * Filter availItems by filter
     *
     * @Route("/filter/{filter}", name="inventory_filter")
     * @Method("GET")
     *
     */
     public function filterAction(Request $request, $filter, $loginMessage = null)
     {
        $em = $this->getDoctrine()->getManager();
        $avalStatus = $em->getRepository('AppBundle:Status')->findOneByName('AVAL'); //place whatever you available status name is here
        $inventories = $em->getRepository('AppBundle:Inventory')->findByItemStatus($avalStatus->getId());

        $returnItems = [];

        foreach ($inventories as $inventory)
        {
            if ($inventory->getItemType() == $filter)
            {
                $returnItems[] = $inventory;
            }
        }

        return $this->render('default/index.html.twig', array (
            'inventories' => $returnItems,
            'loginMessage' => $loginMessage
        ));
     }

     /**
      * @Route("/userdownload", name="user-download")
      **/
    public function downloadFileActionUser() {
        $response = new BinaryFileResponse('files/user-guide.pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'user-guide');
        return $response;
    }

     /**
      * @Route("/admindownload", name="admin-download")
      **/
    public function downloadFileActionAdmin() {
        $response = new BinaryFileResponse('files/admin-guide.pdf');
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'admin-guide');
        return $response;
    }

 }
