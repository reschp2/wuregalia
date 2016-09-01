<?php
/* *********************************************
    LdapSessionUserProvider.php
    Is basically the LdapUserProvider changed in order
    to work with SessionUsers
************************************************ */
namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Ldap\Exception\ConnectionException;
use Symfony\Component\Ldap\LdapClientInterface;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class LdapSessionUserProvider implements UserProviderInterface
{
    private $ldap;
    private $baseDn;
    private $searchDn;
    private $searchPassword;
    private $defaultRoles;
    private $defaultSearch;
    private $em; //doctrine entity manager

    public function __construct(LdapClientInterface $ldap, $em, $baseDn, $searchDn = null, $searchPassword = null, array $defaultRoles = array(), $uidKey = 'sAMAccountName',
 $filter = '({uid_key}={username})')
    {
        $this->ldap = $ldap;
        $this->em = $em;
        $this->baseDn = $baseDn;
        $this->searchDn = $searchDn;
        $this->searchPassword = $searchPassword;
        $this->defaultRoles = $defaultRoles;
        $this->defaultSearch = str_replace('{uid_key}', $uidKey, $filter);
    }


    public function loadUserByUsername($username)
    {
        try {
            $this->ldap->bind($this->searchDn, $this->searchPassword);
            $username = $this->ldap->escape($username, '', LDAP_ESCAPE_FILTER);
            $query = str_replace('{username}', $username, $this->defaultSearch);
            $search = $this->ldap->find($this->baseDn, $query);
        } catch (ConnectionException $e) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username), 0, $e);
        }

        if (!$search) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }

        if ($search['count'] > 1) {
            throw new UsernameNotFoundException('More than one user found');
        }

        $user = $search[0];

        $loadedUser = $this->loadUser($username, $user);
        $loadedUser = $this->checkDatabase($loadedUser); //check if user is in db, check if they have any other roles
        return $loadedUser;
    }

    public function loadUser($username, $result) #$result is the ldap result from querying the directory with username
    {
        $roles = $this->defaultRoles;
        $userInfo = $this->getUserInfo($result, $username); 
        $fname = $userInfo['fname'];
        $lname = $userInfo['lname'];
        $email = $userInfo['email'];

        return new SessionUser($username, $fname, $lname, $email, $roles);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SessionUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return new SessionUser($user->getUsername(), $user->getFname(), $user->getLname(), $user->getEmail(), $user->getRoles());
    }

    public function supportsClass($class)
    {
        return $class === 'AppBundle\Security\SessionUser';
    }

    //Check to see if user (SessionUser type) is in db, if not, add them in
    public function checkDatabase($userToken)
    {
        $userRepos = $this->em->getRepository('AppBundle:User'); 
        $username = $userToken->getUsername();
        $user = $userRepos->findOneByUsername($username); //check if user already in database
        if(!$user)  //add user to database if needed
        {
            $this->addUser($userToken);
        }
        else if($user->isAdmin())
        {
            $userToken->addRole('ROLE_ADMIN');
        }
        return $userToken;
    }

    //Place user in database with information from LDAP directory that is stored in token
    protected function addUser($tok) 
    {
        $user = new User(); //note that this is a AppBundle/Entity/User object
        $user->setUsername($tok->getUsername());
        $user->setLname($tok->getLname());
        $user->setEmail($tok->getEmail());
        $user->setFname($tok->getFname());
        $this->em->persist($user);
        $this->em->flush();
    }

    //Extract the user information from the ldap result
    //Return an associative array with the keys: fname, lname, email
    protected function getUserInfo($result, $username) 
    {
        //extract all the info from result
        $displayName = $result['displayname'][0];
        $split = explode(", ", $displayName);
        if(count($split) == 2) 
        {
            $lname = $split[0];
            $fname = explode(" ", $split[1])[0];
        }
        else
        {
            $split = explode(" ", $split[0]);
            $lname = $split[0];
            $fname = $split[1];
        }

        if(isset($result['mail'])) 
        {
            $email = $result['mail'][0];
        }
        else 
        {
            $email = $username . '@mailbox.winthrop.edu';
        }
        
        return array('fname'=>$fname, 'lname'=>$lname, 'email'=>$email);
    }

}
      
?>
