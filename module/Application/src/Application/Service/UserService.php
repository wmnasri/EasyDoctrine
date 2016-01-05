<?php
namespace Application\Service;

use Application\Entity\User;
use Application\Entity\Phonenumbers;

class UserService
{
    protected $em;
    
    public function setEntityManager($em) 
    {
        $this->em = $em;
        return $this;
    }
    
    public function saveUser ($request, $id = null)
    {
        $user = $this->getUser($id);
        $user->getPhonenumbers()->clear();
        $phoneNumbersPost = $request->getPost('phoneNumber');
        
        $contacts =  array_map(function ($phoneNum) {
                $phoneNumber = new Phonenumbers();
                $phoneNumber->setNumber($phoneNum);
                return $phoneNumber;
            }, 
            array_filter($phoneNumbersPost)
        );      
        foreach ($contacts as $contact) {
            $user->setPhonenumbers($contact);
        } 
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
