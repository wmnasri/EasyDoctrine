<?php
namespace Application\Service;

use Application\Entity\User;
use Application\Entity\Phonenumbers;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

class UserService
{
    /**
     * 
     * @var EntityManager
     */
    protected $em;
    
    public function setEntityManager($em) 
    {
        $this->em = $em;
        return $this;
    }
    
    public function saveUser ($request, $id = null)
    {
        $user = $this->getUser($id);
        $arrayCollection = new ArrayCollection();
        $phoneNumbersPost = $request->getPost('phoneNumber');
        
        $phoneNumbersIds = array_map( function ($val) {
               return ($val) ? $val : rand(-100, -1);
            },
           $request->getPost('ids')
        );
        
        $phoneArray = array_combine($phoneNumbersIds, $phoneNumbersPost);
        
        foreach (array_filter($phoneArray) as $key => $val) {
            $phoneNumber = $this->getPhoneNumber ($user, $key);
            $phoneNumber->setNumber($val);
            $arrayCollection->add($phoneNumber); 
        }
        
        $user->setPhonenumbers($arrayCollection);
        $user->setFullName($request->getPost('fullname'));
    
        $this->em->persist($user);
        $this->em->flush();
        
        return $user->getId();
    }
    
    public function getUser ($id = null)
    {
        if ($id) {
             return  $this->em->find(User::class, $id);
         } else {
             return new User();
         }
    }
    
    public function getPhoneNumber ($user, $id)
    {
        foreach ($user->getPhonenumbers() as $element) {
            if ($element->getId() == $id) {
                return $this->em->find(Phonenumbers::class, $id);
            }
        }
        return new Phonenumbers();
    }
    
    public function getAllUser () 
    {
       return  $this->em->getRepository(User::class)->findAll();
    }
    
    public function deleteUser ($user) 
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
