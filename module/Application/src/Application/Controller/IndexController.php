<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{

    public function indexAction()
    {
        $userService = $this->getServiceLocator()->get('user-service');
        $users = $userService->getAllUser();

        return new ViewModel(array('users' => $users));
    }

    public function saveAction()
    {        
        $userService = $this->getServiceLocator()->get('user-service');
        $id = (int) $this->params()->fromRoute('id', null);
        if ($this->request->isPost()) {
            $userService->saveUser($this->request, $id);

            return $this->redirect()->toRoute('home');
        }
        
        return new ViewModel(['user' => $userService->getUser($id)]);
    }

    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', null);
        $userService = $this->getServiceLocator()->get('user-service');
        $user = $userService->getUser($id);

        if ($this->request->isPost()) {
            $userService->deleteUser($user);
            
            return $this->redirect()->toRoute('home');
        }

        return new ViewModel(array('user' => $user));
    }
}