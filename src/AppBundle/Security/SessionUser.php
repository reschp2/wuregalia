<?php
/* *****************************************
    SessionUser
    Holds the sesion info about the current 
    User (that is logged in)
******************************************** */
namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class SessionUser implements UserInterface
{
    private $username;
    private $roles;
    private $fname;
    private $lname;
    private $email;

    public function __construct($username, $fname, $lname, $email, array $roles)
    {
        $this->username = $username;
        $this->roles = $roles;
        $this->fname = $fname;
        $this->lname = $lname;
        $this->email = $email;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getFname()
    {
        return $this->fname;
    }

    public function getLname()
    {
        return $this->lname;
    }

    public function getEmail()
    {
        return $this->email;
    }


    public function getPassword()
    {
    }

    public function getSalt()
    {
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function addRole($role)
    {
        array_push($this->roles, $role);
    }


    public function eraseCredentials()
    {
    }
    public function __toString()
    {
        return (string) $this->getUsername();
    }
}
?>
