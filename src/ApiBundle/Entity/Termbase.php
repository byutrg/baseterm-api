<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * Description of Termbase
 *
 * @author James Hayes <james.s.hayes@gmail.com>
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deleted_at")
 */
class Termbase
{
	/**
	 *
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
	 * @var string $name
	 * @ORM\Column(type="string")
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
}
