<?php
/* *********************************************************
    LoginListener
    An event listener which listens to login related events
    THIS LISTENER IS NO LONGER NEEDED, EVERYTHING IT DOES
    IS DONE IN LdapSessionUserProvider
************************************************************ */
namespace AppBundle\EventListener;

use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class LoginListener
{
    protected $em;  //Doctrine Entity Manager
    protected $userToken; //User returned from token storage is of type Symfony\Component\Security\Core\User\User
   
    /* ----------------------------------------------------------------
        params:
        EntityManager $em
        TokenStorageInterface $tokenStarage
        LdapClientInterface $ldap
        string $searchDn, $searchPassword, $baseDn, $searchQuery
    ----------------------------------------------------------- */
    public function __construct($em, $tokenStorage) 
    {
       $this->em = $em; 
       $this->userToken = $tokenStorage->getToken()->getUser();
    }

    /* -----------------------------------------------------------
        onInteractiveLogin()
        Once the use actually logs in (as in enter there username
        and password successfully) we need to see if user is in 
        the app database, if not we add them to it.
    --------------------------------------------------------------- */

    //Check if user is already in database, if not, add them
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $userRepos = $this->em->getRepository('AppBundle:User'); 
        $username = $this->userToken->getUsername();
        $user = $userRepos->findOneByUsername($username); //check if user already in database
        if(!$user)  //add user to database if needed
        {
            $this->addUser();
        }
    }

    //Place use in database with information from LDAP directory that is stored in token
    protected function addUser() 
    {
        $tok = $this->userToken;
        $user = new User(); //note that this is a AppBundle/Entity/User object
        $user->setUsername($tok->getUsername());
        $user->setLname($tok->getLname());
        $user->setEmail($tok->getEmail());
        $user->setFname($tok->getFname());
        $this->em->persist($user);
        $this->em->flush();
    }
}

?>
