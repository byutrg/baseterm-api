<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiBundle\Doctrine\RandomIdGenerator;
/**
 * Description of Termbase
 *
 * @author James Hayes <james.s.hayes@gmail.com>
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deleted_at")
 */
class Termbase
{
//	/**
//	 * @var integer $key
//	 * @ORM\Id
//	 * @ORM\Column(type="integer")
//	 * @ORM\GeneratedValue(strategy="AUTO")
//	 */
//	protected $key;
	
	/**
	 * @var string $id
	 * @ORM\Id
	 * @ORM\Column(type="guid", unique=true)
	 * @ORM\GeneratedValue(strategy="CUSTOM")
	 * @ORM\CustomIdGenerator(class="ApiBundle\Doctrine\RandomIdGenerator")
	 */
	protected $id;
	
	/**
	 * @var string $name
	 * @ORM\Column(type="string", unique=true)
	 */
	protected $name;
	
	/**
	 * @var string $working_language
	 * @ORM\Column(type="string")
	 */
	protected $working_language;
	
	/**
	 * @var \DateTime $deleted_at
	 * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
	 */
	protected $deleted_at;
	
	/**
	 * @ORM\OneToMany(targetEntity="Entry", mappedBy="termbase")
	 * @ORM\JoinColumn(name="entry_id", referencedColumnName="id")
	 */
	protected $entries;
	
	/**
	 * @ORM\OneToMany(targetEntity="Person", mappedBy="termbase")
	 * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $people;

    /**
     * Get id
     *
     * @return guid
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Termbase
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
     * Set workingLanguage
     *
     * @param string $workingLanguage
     *
     * @return Termbase
     */
    public function setWorkingLanguage($workingLanguage)
    {
        $this->working_language = $workingLanguage;

        return $this;
    }

    /**
     * Get workingLanguage
     *
     * @return string
     */
    public function getWorkingLanguage()
    {
        return $this->working_language;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Termbase
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deleted_at = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->entries = new \Doctrine\Common\Collections\ArrayCollection();
        $this->people = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add entry
     *
     * @param \ApiBundle\Entity\Entry $entry
     *
     * @return Termbase
     */
    public function addEntry(\ApiBundle\Entity\Entry $entry)
    {
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Remove entry
     *
     * @param \ApiBundle\Entity\Entry $entry
     */
    public function removeEntry(\ApiBundle\Entity\Entry $entry)
    {
        $this->entries->removeElement($entry);
    }

    /**
     * Get entries
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Add person
     *
     * @param \ApiBundle\Entity\Person $person
     *
     * @return Termbase
     */
    public function addPerson(\ApiBundle\Entity\Person $person)
    {
        $this->people[] = $person;

        return $this;
    }

    /**
     * Remove person
     *
     * @param \ApiBundle\Entity\Person $person
     */
    public function removePerson(\ApiBundle\Entity\Person $person)
    {
        $this->people->removeElement($person);
    }

    /**
     * Get people
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPeople()
    {
        return $this->people;
    }
}
