<?php

namespace SMARTASK\HomeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use SMARTASK\UserBundle\Entity\User ;

/**
 * Task
 *
 * @ORM\Table(name="task")
 * @ORM\Entity(repositoryClass="SMARTASK\HomeBundle\Repository\TaskRepository")
 */
class Task
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Many Tasks have Many Users.
     * @ORM\ManyToMany(targetEntity="SMARTASK\UserBundle\Entity\User", mappedBy="tasks")
     */
    private $users;
     
    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255, unique=true)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="localisation", type="string", length=255, nullable=true)
     */
    private $localisation;

    /**
     * One Task has One Groupe.
     * @ORM\ManyToOne(targetEntity="SMARTASK\HomeBundle\Entity\Groupe")
     * @ORM\JoinColumn(name="group_id", referencedColumnName="id")
     */
    private $group;

    /**
     * One Task has One resp.
     * @ORM\ManyToOne(targetEntity="SMARTASK\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="resp_id", referencedColumnName="id")
     */
    private $resp;
    
    /**
     * One Task has One Manager.
     * @ORM\ManyToOne(targetEntity="SMARTASK\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="manager_id", referencedColumnName="id")
     */
    private $manager;

    /**
     * @var datetime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var time
     *
     * @ORM\Column(name="time", type="time", nullable=true)
     */
    private $time;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="isalarmeon", type="integer", nullable=true)
     */
    private $isalarmeon;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return Task
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set localisation
     *
     * @param string $localisation
     *
     * @return Task
     */
    public function setLocalisation($localisation)
    {
        $this->localisation = $localisation;

        return $this;
    }

    /**
     * Get localisation
     *
     * @return string
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }

    /**
     * Set group
     *
     * @param Group $group
     *
     * @return Task
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Set resp
     *
     * @param Contact $resp
     *
     * @return Task
     */
    public function setResp($resp)
    {
        $this->resp = $resp;

        return $this;
    }

    /**
     * Get manager
     *
     * @return User
     */
    public function getManager()
    {
        return $this->manager;
    }
    /**
     * Set manager
     *
     * @param Contact $manager
     *
     * @return Task
     */
    public function setManager($manager)
    {
    	$this->manager = $manager;
    
    	return $this;
    }
    
    /**
     * Get resp
     *
     * @return Contact
     */
    public function getResp()
    {
    	return $this->resp;
    }
    /**
     * Set date
     *
     * @param date $datetime
     *
     * @return Task
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return datetime
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * Set description
     *
     * @param time $time
     *
     * @return Task
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time
     *
     * @return time
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Task
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set isalarmeon
     *
     * @param integer $isalarmeon
     *
     * @return Task
     */
    public function setIsalarmeon($isalarmeon)
    {
        $this->isalarmeon = $isalarmeon;

        return $this;
    }

    /**
     * Get isalarmeon
     *
     * @return int
     */
    public function getIsalarmeon()
    {
        return $this->isalarmeon;
    }
	public function getUsers() {
		return $this->users;
	}
	public function setUsers($users) {
		$this->users = $users;
		return $this;
	}
	
}

