<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Entry;
use ApiBundle\Form\EntryType;

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
use Symfony\Component\Routing\Annotation\Route;

use Voryx\RESTGeneratorBundle\Controller\VoryxController;

/**
 * Entry controller.
 * @RouteResource("Entry")
 */
class EntryRESTController extends VoryxController
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
				->locateResource("@ApiBundle/Resources/config/schema/entry.json");
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
     * Get a Entry entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     *
     */
    public function getAction(Entry $entity)
    {
        return $entity;
    }
    /**
     * Get all Entry entities.
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
            $entities = $em->getRepository('ApiBundle:Entry')->findBy(array('termbase'=>$termbase_id));
			$entries = array();
            if ($entities) {
                foreach($entities as $entry)
				{
					array_push($entries, json_decode($entry->getData(), true));
				}
				return $entries;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Create a Entry entity.
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
        $entity = new Entry();
		$termbase_id = $request->attributes->get('termbase_id');
		$termbase = $this->getDoctrine()
				->getRepository("ApiBundle:Termbase")
				->find($termbase_id);
		$entity->setTermbase($termbase);
		
		$json_content = $request->getContent();
		
		if($this->validate($json_content))
		{
			$entry_object = json_decode($json_content);
			$em = $this->getDoctrine()->getManager();
			try{
				$em->persist($entity);
				$entry_object->id = $entity->getId();
				$entity->setData(json_encode($entry_object));
				
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
     * Update a Entry entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function putAction(Request $request, Entry $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $jsonContent = $request->getContent();
			if ($this->validate())
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
     * Partial Update to a Entry entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function patchAction(Request $request, Entry $entity)
    {
        return $this->putAction($request, $entity);
    }
    /**
     * Delete a Entry entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function deleteAction(Request $request, Entry $entity)
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
