<?php
/* ---------------------------------------------
    School Entity class
    School of an item

    Author: Noah Weber
------------------------------------------------ */

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 *@ORM\Entity
 *@ORM\Table(name="School")
 */
class School
{
    /**
     *@ORM\Column(type="integer", nullable=false)
     *@ORM\Id
     *@ORM\GeneratedValue
     */
    protected $id;

    /**
    * @ORM\OneToMany(targetEntity="Inventory", mappedBy="itemSchool")
    */
    protected $inventoryItems; 

    /**
     *@ORM\Column(type="string", nullable=false, unique=true, length=255)
     */
    protected $name; 
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inventoryItems = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return School
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add inventoryItems
     *
     * @param \AppBundle\Entity\Inventory $inventoryItems
     * @return School
     */
    public function addInventoryItem(\AppBundle\Entity\Inventory $inventoryItems)
    {
        $this->inventoryItems[] = $inventoryItems;

        return $this;
    }

    /**
     * Remove inventoryItems
     *
     * @param \AppBundle\Entity\Inventory $inventoryItems
     */
    public function removeInventoryItem(\AppBundle\Entity\Inventory $inventoryItems)
    {
        $this->inventoryItems->removeElement($inventoryItems);
    }

    /**
     * Get inventoryItems
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInventoryItems()
    {
        return $this->inventoryItems;
    }

    public function __toString()
    {
        return $this->getName();
    }

}
