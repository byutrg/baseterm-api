<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Person;
use ApiBundle\Form\PersonType;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\View as FOSView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Voryx\RESTGeneratorBundle\Controller\VoryxController;

/**
 * Person controller.
 * @RouteResource("Person")
 */
class PersonRESTController extends VoryxController
{
	protected $validationErrors;
	
	public function getErrors()
	{
		$error_message = "";
		foreach ($this->validationErrors as $e)
		{
			$error_message .= "[".$e['property']."]    ".$e['message']."<br>";
		}
		return $error_message;
	}
	
	public function validate($jsonContent)
	{
		$valid = true;
		//Validate against Schema
		$retriever = new \JsonSchema\Uri\UriRetriever;
		$path = $this->get('kernel')
				->locateResource("@ApiBundle/Resources/config/schema/person.json");
		$schema = $retriever->retrieve($path);

		$validator = new \JsonSchema\Validator();
		$validator->check(json_decode($jsonContent), $schema);
		if(!$validator->isValid())
		{
			$this->validationErrors = $validator->getErrors();
			$valid = false;
		}
		
		return $valid;
		//END VALIDATION
	}
	
    /**
     * Get a Person entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     *
     */
    public function getAction(Person $entity)
    {
        return $entity;
    }
    /**
     * Get all Person entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     *
     */
    public function cgetAction(Request $request)
    {
        try {
			$termbase_id = $request->attributes->get('termbase_id');
            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('ApiBundle:Person')->findBy(array('termbase'=>$termbase_id));
			$people = array();
            if ($entities) {
                foreach($entities as $Person)
				{
					array_push($people, json_decode($Person->getData(), true));
				}
				return $people;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Create a Person entity.
     *
     * @View(statusCode=201, serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     *
     */
    public function postAction(Request $request)
    {
        $entity = new Person();
		$termbase_id = $request->attributes->get('termbase_id');
		$termbase = $this->getDoctrine()
			->getRepository("ApiBundle:Termbase")
			->find($termbase_id);
		$entity->setTermbase($termbase);
		
		$jsonContent = $request->getContent();
        if ($this->validate($jsonContent)) 
		{
			$data_object = json_decode($jsonContent);
            $em = $this->getDoctrine()->getManager();
            try{
				$em->persist($entity);
				$data_object->id = $entity->getId();
				$entity->setData(json_encode($data_object));
				$em->flush();
			} catch (\PDOException $e) {
				return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
			} catch (\Exception $e) {
				return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
			}
            return array('created'=>$entity->getId());
        }
		else
		{
			return FOSView::create($this->getErrors(), Codes::HTTP_BAD_REQUEST);
		}
    }
    /**
     * Update a Person entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function putAction(Request $request, Person $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
			$jsonContent = $request->getContent();
            if ($this->validate($jsonContent)) 
			{
				$data_object = json_decode($jsonContent);
				$data_object->id = $entity->getId();
				$entity->setData(json_encode($data_object));
                $em->flush();

                return array('updated'=>$entity->getId());
            }
			else
			{
				return FOSView::create($this->getErrors(), Codes::HTTP_BAD_REQUEST);
			}
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Partial Update to a Person entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function patchAction(Request $request, Person $entity)
    {
        return $this->putAction($request, $entity);
    }
    /**
     * Delete a Person entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function deleteAction(Request $request, Person $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            return null;
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
