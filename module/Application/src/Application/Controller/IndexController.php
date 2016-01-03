<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User;
use Application\Entity\Phonenumbers;

class IndexController extends AbstractActionController
{
    protected $objectManager;

    public function indexAction()
    {
        $users = $this->getObjectManager()->getRepository('\Application\Entity\User')->findAll();

        return new ViewModel(array('users' => $users));
    }

    public function saveAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id) {
            $user = $this->getObjectManager()->find('\Application\Entity\User', $id);
        } else {
            $user = new User();
        }
        
        $user->getPhonenumbers()->clear();
        
        if ($this->request->isPost()) {
           $phoneNumbersPost = $this->getRequest()->getPost('phoneNumber');
            
           $contacts =  array_map(function ($phoneNum) {
                   $phoneNumber = new Phonenumbers();
                   $phoneNumber->setNumber($phoneNum);
                   return $phoneNumber;
            }, array_filter($phoneNumbersPost));
           
            foreach ($contacts as $contact) {
                 $user->setPhonenumbers($contact);
            }
            
            $user->setFullName($this->getRequest()->getPost('fullname'));
    
            $this->getObjectManager()->persist($user);
            $this->getObjectManager()->flush();
            $newId = $user->getId();

            return $this->redirect()->toRoute('home');
        }
        return new ViewModel(['user' => $user]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $user = $this->getObjectManager()->find('\Application\Entity\User', $id);

        if ($this->request->isPost()) {
            $this->getObjectManager()->remove($user);
            $this->getObjectManager()->flush();

            return $this->redirect()->toRoute('home');
        }

        return new ViewModel(array('user' => $user));
    }

    protected function getObjectManager()
    {
        if (!$this->objectManager) {
            $this->objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }

        return $this->objectManager;
    }
}