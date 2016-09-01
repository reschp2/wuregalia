<?php
/**
 * Created by PhpStorm.
 * User: mohammedkashkari
 * Date: 3/20/16
 * Time: 10:24 PM
 */
namespace AppBundle\Controller;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Entity\Inventory;
use AppBundle\Entity\Status;




class donationRequestController extends Controller
{
    /**
     * @Route("/donation_request", name="donationRequest")
     */
    public function indexAction(Request $request)
    {
        $donationRequest = new Inventory();

        // Create the special request form
        $form = $this->createFormBuilder($donationRequest)
            ->add('itemType')
            ->add('itemColor')
            ->add('itemSchool')
            ->add('itemMajor')
            ->add('itemSize', TextType::class,  array('attr' => array('placeholder' => "Example : 5'8'' ")))
            ->add('itemDescription',TextareaType::class , array('attr' => array('cols' => '70', 'rows' => '5','placeholder' => 'Please specify the condition of the item'), ))
            ->add('save', SubmitType::class, array('label' => 'Submit your donation request'))
            ->getForm();
        $form->handleRequest($request);

        // when the form is submitted and all fields are valid.
        if ($form->isSubmitted() && $form->isValid()) {
            // set the status of the request to 'PENDING_DONATION'
            $Status = $this->getDoctrine()->getRepository('AppBundle:Status')->findOneBy(array('name'  => 'PENDING_DONATION'));
            $donationRequest->setItemStatus($Status);

            // set the user of request to the user who is logged in.
            $User = $this->getDoctrine()->getRepository('AppBundle:User')->findOneBy(array('username'  => $this->getUser()));
            $donationRequest->setUser($User);

            // put the request on the inventory table.
            $em = $this->getDoctrine()->getManager();
            $em->persist($donationRequest);
            $em->flush();

            // send a notification email to the admin
            /*$message = \Swift_Message::newInstance()
                ->setSubject('New Donation Request Submitted to WU Regalia Closet')
                ->setFrom('wuregalia@gmail.com')
                ->setTo('admin email address goes here')
                ->setBody(
                    $this->renderView(
                        'emailsNotifications/donationRequest/adminNewDonationRequest.txt.twig',array('donationRequest' => $donationRequest)
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);*/

            $user = $this->getUser();
            // send a confirmation email to the user
            $message = \Swift_Message::newInstance()
                ->setSubject('Donation Request Confirmation')
                ->setFrom('wuregalia@gmail.com')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        'emailsNotifications/donationRequest/userDonationRequestReceived.txt.twig', array('donationRequest' => $donationRequest)
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($message);

            // go to the confirmation page
            return $this->render('donationRequest/success.html.twig');
        }

        // show the form, including an error if it was submitted and not valid.
        return $this->render('donationRequest/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
    /**
     * @Route("/admin/donation_request", name="adminDonationRequest")
     */
    public function adminAction(Request $request)
    {
        // find all inventory records with a status of pending donation request
        $Status = $this->getDoctrine()->getRepository('AppBundle:Status')->findOneBy(array('name'  => 'PENDING_DONATION'));
        $donationRequestsList = $this->getDoctrine()->getRepository('AppBundle:Inventory')->findBy(array('itemStatus' => $Status ));
        return $this->render('donationRequest/admin.html.twig',array(
            'donationRequests' => $donationRequestsList
        ));
    }
    /**
     * @Route("/admin/donation_request/status/{inventoryRequest}", name="acceptDonationRequest")
     */
    public function adminChangeStatusAction(Request $request, $inventoryRequest)
    {
        // set the status of the request to pending arrival when the donation request is accepted
        $em = $this->getDoctrine()->getManager();
        $record = $em->getRepository('AppBundle:Inventory')->find($inventoryRequest);
        $Status = $em->getRepository('AppBundle:Status')->findOneBy(array('name' => 'PENDING_ARRIVAL' ));
        $record->setItemStatus($Status);

        $em->flush();

        $user = $this->getUser();
        // send an email to the user to let him/her know that the request has been accepted
        $message = \Swift_Message::newInstance()
            ->setSubject('Donation Request Accepted')
            ->setFrom('wuregalia@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emailsNotifications/donationRequest/userDonationRequestAccepted.txt.twig', array('donationRequest' => $record)
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);

        // show all of the inventory records with a status of pending donation request
        $Status = $this->getDoctrine()->getRepository('AppBundle:Status')->findOneBy(array('name'  => 'PENDING_DONATION'));
        $donationRequestsList = $this->getDoctrine()->getRepository('AppBundle:Inventory')->findBy(array('itemStatus' => $Status ));
        return $this->render('donationRequest/admin.html.twig',array(
            'donationRequests' => $donationRequestsList));
    }


    /**
     * @Route("/admin/donation_request/reject/{inventoryRequest}", name="rejectDonationRequest")
     */
    public function adminRejectSpecialRequestAction(Request $request,$inventoryRequest)
    {
        // delete the request when it is rejected
        $em = $this->getDoctrine()->getManager();
        $record = $em->getRepository('AppBundle:Inventory')->find($inventoryRequest);
        $em->remove($record);
        $em->flush();

        $user = $this->getUser();
        // send an email to the user to let him/her know that the request has been rejected
        $message = \Swift_Message::newInstance()
            ->setSubject('Donation Request Rejected')
            ->setFrom('wuregalia@gmail.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emailsNotifications/donationRequest/userDonationRequestRejected.txt.twig', array('donationRequest' => $record)
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);

        // show all of the inventory records with a status of pending donation request
        $Status = $this->getDoctrine()->getRepository('AppBundle:Status')->findOneBy(array('name'  => 'PENDING_DONATION'));
        $donationRequestsList = $this->getDoctrine()->getRepository('AppBundle:Inventory')->findBy(array('itemStatus' => $Status ));
        return $this->render('donationRequest/admin.html.twig',array(
            'donationRequests' => $donationRequestsList));
    }

}