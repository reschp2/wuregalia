<?php
/**
 Peter Resch
 Controllers in this file populate reports and allows conversion of reports to pdf format for printing or downloading
 */
 
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


use AppBundle\Entity\Inventory;
use AppBundle\Entity\Status;
use AppBundle\Entity\User;
use AppBundle\Entity\ItemType;
use AppBundle\Entity\Major;
use AppBundle\Entity\School;
use AppBundle\Entity\Color;
use AppBundle\Entity\Reservation;




class ReportsController extends Controller
{
    /**
     * @Route("/admin/reports", name="reports")
     * displays list of possible reports
     */
    public function indexAction(Request $request)
    {
    	return $this->render('reports/index.html.twig');	    
    }
    
    /**
     * @Route("/reports/users", name="showUsers")
     * displays all users
     */
    public function showUsers(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:User')->findAll();

        return $this->render('reports/users.html.twig', array(
            'users' => $users,
        ));
    }
    
    /**
     * @Route("/reports/admins", name="showAdmins")
     * displays administrators
     */
    public function showAdmins(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('AppBundle:User')->findByAdminpriv(1);

        return $this->render('reports/users_admins.html.twig', array(
            'users' => $users,
        ));
    }
    
    /**
     * @Route("/reports/registeredUsers", name="showRegisteredUsers")
     * displays administrators
     */
    public function showRegisteredUsers(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findByAdminpriv(0);

        return $this->render('reports/users_registered.html.twig', array(
            'users' => $users,
        ));
    }
    
    /**
     * @Route("/reports/users/rentalHistory/{userID}", name="showUserRentalHistory")
     * display rental history of selected user (userID)
     */
    public function showUserRentalHistory(Request $request, $userID = 0)
    {
    	$em = $this->getDoctrine()->getManager();
        $reservations = $em->getRepository('AppBundle:Reservation')->findByUserid($userID);

        return $this->render('reports/users.rentalHistory.html.twig', array(
            'user' => $reservations,
        ));
    }
    
    /**
     * @Route("/reports/inventory", name="showInventory")
     * displays all inventory items, both rented and availible
     */
    public function showInventory(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$itemStatuses = $em->getRepository('AppBundle:Status')->findby(array('name' => array('AVAL','PENDING_DONATION','RENTED','PENDING_ARRIVAL')));
        $itemStatusIDs = array();
        foreach ($itemStatuses as $Status)
        {
		$itemStatusIDs[] = $Status->getId();
        }	
        $inventory = $em->getRepository('AppBundle:Inventory')->findBy(array('itemStatus' => $itemStatusIDs));

        return $this->render('reports/inventory.html.twig', array(
            'items' => $inventory,
        ));
    }
    
    /**
     * @Route("/reports/inventory/availible", name="showAvailibleInventory")
     * displays availible inventory items
     */
    public function showAvailibleInventory(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$avalStatus = $em->getRepository('AppBundle:Status')->findOneBy(array('name' => array('AVAL')));
        $inventory = $em->getRepository('AppBundle:Inventory')->findByItemStatus($avalStatus->getId());

        return $this->render('reports/inventory_availible.html.twig', array(
            'items' => $inventory,
        ));
    }
    
    /**
     * @Route("/reports/inventory/rented", name="showRentedInventory")
     * displays rented inventory items
     */
    public function showRentedInventory(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $rentedStatus = $em->getRepository('AppBundle:Status')->findOneBy(array('name' => array('RENTED')));
        $inventory = $em->getRepository('AppBundle:Inventory')->findByItemStatus($rentedStatus->getId());

        return $this->render('reports/inventory_rented.html.twig', array(
            'items' => $inventory,
        ));
    }
    
    /**
     * @Route("/reports/inventory/donations", name="showDonationInventory")
     * displays pending donations 
     */
    public function showDonationInventory(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $rentedStatus = $em->getRepository('AppBundle:Status')->findOneBy(array('name' => array('PENDING_DONATION')));
        $inventory = $em->getRepository('AppBundle:Inventory')->findByItemStatus($rentedStatus->getId());

        return $this->render('reports/inventory_donations.html.twig', array(
            'items' => $inventory,
        ));
    }
    
    /**
     * @Route("/reports/users/donationHistory/{userID}", name="showUserDonationHistory")
     * display donation history of selected user (userID)
     */
    public function showUserDonationHistory(Request $request, $userID = 0)
    {
    	$em = $this->getDoctrine()->getManager();
        $itemStatuses = $em->getRepository('AppBundle:Status')->findby(array('name' => array('AVAL','PENDING_DONATION','RENTED','PENDING_ARRIVAL')));
        $itemStatusIDs = array();
        foreach ($itemStatuses as $Status)
        {
        	$itemStatusIDs[] = $Status->getId();
	}	
	$donations = $em->getRepository('AppBundle:Inventory')->findBy(array(
		'user'=>$userID,
		'itemStatus' => $itemStatusIDs,
	));
	
        return $this->render('reports/users.donationHistory.html.twig', array(
            'user' => $donations,
        ));
    }
  
    /**
     * @Route("/reports/specialRequests", name="showSpecialRequests")
     * displays pending donations 
     */
    public function showSpecialRequests(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $requestStatus = $em->getRepository('AppBundle:Status')->findOneBy(array('name' => array('PENDING_SPECIAL')));
        $inventory = $em->getRepository('AppBundle:Inventory')->findByItemStatus($requestStatus->getId());

        return $this->render('reports/specialRequests.html.twig', array(
            'items' => $inventory,
        ));
    }
    
    /**
     * @Route("/reports/inventory/pendingArrivals", name="showPendingArrivals")
     * displays pending donations 
     */
    public function showPendingArrivals(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $pendingArrivalStatus = $em->getRepository('AppBundle:Status')->findOneBy(array('name' => array('PENDING_ARRIVAL')));
        $inventory = $em->getRepository('AppBundle:Inventory')->findByItemStatus($pendingArrivalStatus->getId());

        return $this->render('reports/inventory_pendingArrivals.html.twig', array(
            'items' => $inventory,
        ));
    }
    
    /**
     * @Route("/reports/rentalHistory", name="showRentalHistory")
     * displays rental history 
     */
    public function showRentalHistory(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
        $reservations = $em->getRepository('AppBundle:Reservation')->findAll();

        return $this->render('reports/rentalHistory.html.twig', array(
            'reservations' => $reservations,
        ));
    }
}
