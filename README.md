# EasyDoctrine : ZF2 with Doctrine 2 ORM
=======================

Creation Steps
--------------

1.Create ZF2 project from skeleton using composer

```
curl -s https://getcomposer.org/installer | php --
php composer.phar create-project -sdev --repository-url="http://packages.zendframework.com" zendframework/skeleton-application zf2-example-doctrine2
```

2.Update composer.json to require Doctrine 2

```
php composer.phar self-update
php composer.phar require doctrine/doctrine-module:dev-master
php composer.phar require doctrine/doctrine-orm-module:dev-master
```

3.Install ZF Dev tools

```
php composer.phar require zendframework/zend-developer-tools:dev-master
```

4.Copy ZF Dev tools autoload config to application config and add modules

```
cp vendor/zendframework/zend-developer-tools/config/zenddevelopertools.local.php.dist config/autoload/zdt.local.php
```

Edit config/application.config.php:

```
...
'modules' => array(
    'ZendDeveloperTools',
    'DoctrineModule',
    'DoctrineORMModule',
    'Application',
),
...
```

5.Add first entity User

New file module/Application/src/Application/Entity/User.php:

```
<?php

namespace Application\Entity;
use Doctrine\ORM\Mapping as ORM;
/** @ORM\Entity */
class User {
    /**
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    * @ORM\Column(type="integer")
    */
    protected $id;

    /** @ORM\Column(type="string") */
    protected $fullName;

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
    }
}
```

6.Add the Doctrine Driver to application config

Edit config/module.config.php:

```
return array(
    'doctrine' => array(
        'driver' => array(
            'application_entities' => array(
                'class' =>'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/Application/Entity')
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Application\Entity' => 'application_entities'
                )
            )
        )
    ),
    ...
```

You should now see the new entity in the ZF2 Dev tool bar in the doctrine section at the bottom of the screen.

7.Add database config for Doctrine

New file local.php:

```
<?php

return array(
);
```

New file config/autoload/doctrine.local.php (for local MySql):

```
<?php

return array(
  'doctrine' => array(
    'connection' => array(
      'orm_default' => array(
        'driverClass' =>'Doctrine\DBAL\Driver\PDOMySql\Driver',
        'params' => array(
          'host'     => 'localhost',
          'port'     => '3306',
          'user'     => 'username',
          'password' => 'password',
          'dbname'   => 'database',
)))));
```

8.Validate the schema against the current DB (will fail since you haven't got any schema)

```
./vendor/bin/doctrine-module orm:validate-schema
```

9.Generate the schema

This will apply the ORM generated schema to the DB

```
./vendor/bin/doctrine-module orm:schema-tool:create
```

10.Update routes for CRUD actions

Edit module/Application/config/module.config.php:

```
...
'user' => array(
    'type'    => 'segment',
    'options' => array(
        'route'    => '/user[/][:action][/:id]',
        'constraints' => array(
            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
            'id'     => '[0-9]+',
        ),
        'defaults' => array(
            'controller' => 'Application\Controller\Index',
            'action'     => 'index',
        ),
    ),
),
...
```

11.Update IndexController for CRUD actions

Edit module/Application/src/Application/Controller/IndexController.php:

```
<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Entity\User;

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
        if ($this->request->isPost()) {
            $user->setFullName($this->getRequest()->getPost('fullname'));

            $this->getObjectManager()->persist($user);
            $this->getObjectManager()->flush();
            $newId = $user->getId();

            return $this->redirect()->toRoute('home');
        }
        return new ViewModel();
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
```

12.Add views for CRUD actions

Edit module/Application/view/application/index/index.phtml:

```
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Users</h3>
            </div>
            <div class="panel-body">
                <a data-toggle="modal" data-target="#Modal-add" href="#">Add User</a>

                <?php if (isset($users)) : ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th>Id</th>
                        <th>Full name</th>
                        <th></th>
                    </tr>
                    </thead>
                <?php foreach($users as $user): ?>
                    <tbody>
                    <tr>
                        <td><?php echo $user->getId(); ?></td>
                        <td><?php echo $user->getFullName(); ?></td>
                        <td>
                            <a data-toggle="modal" data-target="#Modal-edit" href="#">Edit</a> |
                            <a data-toggle="modal" data-target="#myModal" href="#">Delete</a>
                            <?= $this->partial('edit.phtml',['user' => $user])?>
                    		<?= $this->partial('delete.phtml',['user' => $user])?>
                        </td>
                    </tr>
                    </tbody>
                <?php endforeach; ?>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->partial('add.phtml')?>

```

Add partial view  module/Application/view/application/index/add.phtml:

```
<div id ='Modal-add' class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add new User</h4>
      </div>
      <form method="post" action ='<?= $this->url('user', array('action'=>'add'));?>'>
      <div class="modal-body">       
        Full name: <input type="text" name="fullname">
      </div>
      <div class="modal-footer">
        <input type="submit" class="btn btn-default" value="Submit">
      </div>
      </form>
    </div>
  </div>
</div>
```
Add partial view module/Application/view/application/index/edit.phtml:

```
<div id ='Modal-edit' class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Edit User</h4>
      </div>
      <form method="post" action='<?= $this->url('user', array('action'=>'edit', 'id' => $user->getId()));?>'>
          <div class="modal-body">       
              fullname: <input type="text" name="fullname" value="<?php echo $user->getFullname(); ?>">
          </div>
          <div class="modal-footer">
            <input type="submit" class="btn btn-default" value="Validate">
          </div>
       </form>
    </div>
  </div>
</div>
```
Add partial view module/Application/view/application/index/delete.phtml:

```
<div id ='myModal' class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Delete User</h4>
      </div>
      <div class="modal-body">
        Are you sure you want to delete user <?= $user->getFullname(); ?>? <br/> 
      </div>
      <div class="modal-footer">
         <form method="post" action='<?= $this->url('user', array('action'=>'delete', 'id' => $user->getId()));?>'>
          <input type="submit" class="btn btn-default" value="Delete">
         </form>
      </div>
    </div>
  </div>
</div>
```

This covers basic CRUD actions using Doctrine 2 ORM in ZF2.


## Association Mapping

This chapter explains mapping associations between objects.

Instead of working with foreign keys in your code, you will always work with references to objects instead and Doctrine will convert those references to foreign keys internally.

A reference to a single object is represented by a foreign key.

A collection of objects is represented by many foreign keys pointing to the object holding the collection

### One-To-Many, Unidirectional with Join Table

A unidirectional one-to-many association can be mapped through a join table. From Doctrine's point of view, it is simply mapped as a unidirectional many-to-many whereby a unique constraint on one of the join columns enforces the one-to-many cardinality.

The following example sets up such a unidirectional one-to-many association:

- Class Entity\User

```php
<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @ORM\ManyToMany(targetEntity="Application\Entity\Phonenumbers", cascade={"persist", "remove")
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
	
	// .. Getter and Setter
	....
	
	/**
     * @param Phonenumbers $value
     */
    public function setPhonenumbers(Phonenumbers $value) 
    {
        if (!$this->phonenumbers->contains($value)) {
            $this->phonenumbers->add($value);
        }
    }
}
```

- Class Entity\Phonenumbers

```php
<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="phonenumbers")
 */
class Phonenumbers
{
	/**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id", type="integer")
     */
    protected $id;

    /**
     *  @ORM\Column(name="number", type="string") 
     */
    protected $number;
	
	//.. Getter and Setter
	...
}
```

- Generates the following MySQL Schema:

```sql
CREATE TABLE User (
    id INT AUTO_INCREMENT NOT NULL,
    fullname VARCHAR(45) NOT NULL,
    PRIMARY KEY(id)
) ENGINE = InnoDB;

CREATE TABLE users_phonenumbers (
    user_id INT NOT NULL,
    phonenumber_id INT NOT NULL,
    UNIQUE INDEX users_phonenumbers_phonenumber_id_uniq (phonenumber_id),
    PRIMARY KEY(user_id, phonenumber_id)
) ENGINE = InnoDB;

CREATE TABLE Phonenumber (
    id INT AUTO_INCREMENT NOT NULL,
    number VARCHAR(45) NOT NULL,
    PRIMARY KEY(id)
) ENGINE = InnoDB;

ALTER TABLE users_phonenumbers ADD FOREIGN KEY (user_id) REFERENCES User(id);
ALTER TABLE users_phonenumbers ADD FOREIGN KEY (phonenumber_id) REFERENCES Phonenumber(id);
```

Now edit saveAction function in IndexConytroller with the following code : 

```php
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
```

- You find other Association Mapping in [Doctrine] (http://doctrine-orm.readthedocs.org/projects/doctrine-orm/en/latest/reference/association-mapping.html)

Links
-----
* [ZF2](http://framework.zend.com/)
* [Doctrine 2](http://www.doctrine-project.org/)
* [IBM blog](http://www.ibm.com/developerworks/library/os-doctrine-php-zend/)