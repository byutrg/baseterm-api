<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\Doctrine;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Doctrine\ORM\Id\AbstractIdGenerator;
/**
 * Description of RandomIdGenerator
 *
 * @author James Hayes <james.s.hayes@gmail.com>
 */
class RandomIdGenerator extends AbstractIdGenerator
{
	public function generate(\Doctrine\ORM\EntityManager $em, $entity)
	{
		try {
			while(1)
			{
				$id = substr_replace(Uuid::uuid4(), strtolower(get_class($entity))[17], 0,1); //adds a 't','e', or 'p' in front of id, to be XML compliant
				if(!$em->getRepository(get_class($entity))->find($id))
				{
					break;
				}
			}
		} catch (UnsatisfiedDependencyException $e) {
			echo 'Exception: ' . $e->getMessage() . "\n";
		}
		
		return $id;
	}
}