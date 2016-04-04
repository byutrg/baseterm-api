<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * Description of Person
 *
 * @author James Hayes <james.s.hayes@gmail.com>
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deleted_at")
 */
class Person
{
	/**
	 * @var integer $key
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $key;
	
	/**
	 * @var string $id
	 * @ORM\Column(type="string")
	 */
	protected $id;
	
	/**
	 * @var \DateTime $deleted_at
	 * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
	 */
	protected $deleted_at;
	
	/**
	 * @ORM\Column(type="text")
	 */
	protected $data;
}
