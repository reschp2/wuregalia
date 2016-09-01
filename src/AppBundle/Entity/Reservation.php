<?php
/* ---------------------------------------------
    Reservation Entity class
    Holds data about a reservation instance 
    that is persited into database via doctrine

    Author: Noah Weber
------------------------------------------------ */
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *@ORM\Entity
 *@ORM\Table(name="Reservation")
 */
class Reservation
{
    /**
     *@ORM\Column(type="integer", nullable=false)
     *@ORM\Id
     *@ORM\GeneratedValue
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="reservations")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $userid;  //who has made the reservation

    /**
     * @ORM\OneToOne(targetEntity="Inventory")
     * @ORM\JoinColumn(name="inventory_id", referencedColumnName="id", nullable=false, unique=true)
     */
    protected $item; //item reserved

    /**
     *@ORM\Column(type="datetime", nullable=false)
     */
    protected $createDate; //when reservation is made

    /**
     *@ORM\Column(type="datetime", nullable=false)
     */
    protected $dueDate; //when the item is due back

    /**
     *@ORM\Column(type="boolean", nullable=false, options={"default":false})
     */
    protected $approved; //whether or not the reservation has been approved by admin

    //Pass in a Entity/User and Entity/Inventory types as parameters
    public function __construct($user, $item)
    {
        $this->createDate = new \DateTime(); //this defualts to using creation date as default
        $this->dueDate= date_add(new \DateTime(), date_interval_create_from_date_string('14 days')); //due date is 2 weeks after rent
        $this->approved = false;
        $this->userid = $user;
        $this->item = $item;
    }



    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set createDate
     *
     * @param \DateTime $createDate
     * @return Reservation
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;

        return $this;
    }

    /**
     * Get createDate
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return Reservation
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set approved
     *
     * @param boolean $approved
     * @return Reservation
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get approved
     *
     * @return boolean 
     */
    public function getApproved()
    {
        return $this->approved;
    }

    /**
     * Set userid
     *
     * @param \AppBundle\Entity\User $userid
     * @return Reservation
     */
    public function setUserid(\AppBundle\Entity\User $userid)
    {
        $this->userid = $userid;

        return $this;
    }

    /**
     * Get userid
     *
     * @return \AppBundle\Entity\User 
     */
    public function getUserid()
    {
        return $this->userid;
    }

    /**
     * Set item
     *
     * @param \AppBundle\Entity\Inventory $item
     * @return Reservation
     */
    public function setItem(\AppBundle\Entity\Inventory $item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return \AppBundle\Entity\Inventory 
     */
    public function getItem()
    {
        return $this->item;
    }
}
