<?php

namespace SMARTASK\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use SMARTASK\HomeBundle\Entity\Task ;
use SMARTASK\HomeBundle\Entity\Groupe ;
use SMARTASK\HomeBundle\Entity\Contact ;
use FOS\ElasticaBundle\Configuration\Search;

/**
 * User
 *
 * @ORM\Table(name="user")
 *  * @Search(repositoryClass="SMARTASK\UserBundle\Repository\UserRepository")
 * @ORM\Entity(repositoryClass="SMARTASK\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Many Users have Many Groups.
     * @ORM\ManyToMany(targetEntity="SMARTASK\HomeBundle\Entity\Groupe", inversedBy="users")
     * @ORM\JoinTable(name="users_groups")
     */
    protected $groupes;
    
  
    
    /**
     * Many Users have Many Tasks.
     * @ORM\ManyToMany(targetEntity="SMARTASK\HomeBundle\Entity\Task", inversedBy="users")
     * @ORM\JoinTable(name="users_tasks")
     */
    protected $tasks;
    
    /**
     * One User has Many Contacts.
     * @ORM\OneToMany(targetEntity="SMARTASK\HomeBundle\Entity\Contact", mappedBy="user")
     */
    private $contacts;
    
    
    
    
	public function getGroupes() {
		return $this->groupes;
	}
	public function setGroupes($groupes) {
		$this->groupes = $groupes;
		return $this;
	}
	// Notez le singulier, on ajoute une seule catégorie à la fois
	public function addGroupe(Groupe $group)
	{
		// Ici, on utilise l'ArrayCollection vraiment comme un tableau
		$this->groupes[] = $group;
	}
	
	public function removeGroupe(Groupe $group)
	{
		// Ici on utilise une méthode de l'ArrayCollection, pour supprimer la catégorie en argument
		$this->groupes->removeElement($group);
	}
	public function getTasks() {
		return $this->tasks;
	}
	public function setTasks($tasks) {
		$this->tasks = $tasks;
		return $this;
	}
	public function getContacts() {
		return $this->contacts;
	}
	public function setContacts($contacts) {
		$this->contacts = $contacts;
		return $this;
	}
	
}

