<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Phonenumbers;
/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     *  @ORM\Column(name="full_name", type="string") 
     */
    protected $fullName;
    
    /**
     * @ORM\ManyToMany(targetEntity="Application\Entity\Phonenumbers", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="users_phonenumbers",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="phonenumber_id", referencedColumnName="id", unique=true, onDelete="CASCADE")}
     *      )
     */

    private $phonenumbers;

    public function __construct()
    {
        $this->phonenumbers = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFullName()
    {
        return $this->fullName;
    }

    public function setFullName($value)
    {
        $this->fullName = $value;
        return $this;
    }
    
    /**
     * @return ArrayCollection
     */
    public function getPhonenumbers()
    {
        return $this->phonenumbers;
    }
    
    /**
     * @param ArrayCollection $value
     */
    public function setPhonenumbers(ArrayCollection $value) 
    {
        $this->phonenumbers = $value;
        return $this;
    }
}
