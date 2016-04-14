<?php

namespace ApiBundle\Controller;

use ApiBundle\Entity\Termbase;
use ApiBundle\Form\TermbaseType;
use ApiBundle\Converter\RawTBXConverter;

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

use Symfony\Component\Debug\ErrorHandler;

use Voryx\RESTGeneratorBundle\Controller\VoryxController;

/**
 * Termbase controller.
 * @RouteResource("Termbase")
 */
class TermbaseRESTController extends VoryxController
{
	protected $validationErrors = array();
	
	public function errorHandler($errno, $errstr, $errfile, $errline)
	{
		$property = $errno;
		array_push($this->validationErrors, array("property"=>$property, "message"=>$errstr));
	}


	public function getErrors()
	{
		$error_message = "";
		foreach ($this->validationErrors as $e)
		{
			$error_message .= "[".$e['property']."]    ".$e['message']."<br>";
		}
		return $error_message;
	}
	
	public function validateJSON($entity)
	{
		$valid = true;
		//Validate against Schema
		$retriever = new \JsonSchema\Uri\UriRetriever;
		$path = $this->get('kernel')
				->locateResource("@ApiBundle/Resources/config/schema/termbase.json");
		$schema = $retriever->retrieve($path);
		$jsonContent = $this->get('jms_serializer')
				->serialize($entity, 'json');

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
	
	public function validateTBX($tbx_file)
	{
		//Validate TBX
		set_error_handler(array(&$this, "errorHandler"));
		$path = $this->get('kernel')
				->locateResource("@ApiBundle/Resources/config/schema/TBXBasicRNGV02.rng");
		$xml_reader = new \XMLReader();
		$xml_reader->open($tbx_file);
		$xml_reader->setRelaxNGSchema($path);
		while($xml_reader->read())
		{
			$xml_reader->next();
		}
		
		return $xml_reader->isValid();
	}
	
    /**
     * Get a Termbase entity
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @return Response
     *
     */
    public function getAction(Termbase $entity)
    {
        return $entity;
    }
    /**
     * Get all Termbase entities.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Response
     *
     * @QueryParam(name="offset", requirements="\d+", nullable=true, description="Offset from which to start listing notes.")
     * @QueryParam(name="limit", requirements="\d+", default="20", description="How many notes to return.")
     * @QueryParam(name="order_by", nullable=true, array=true, description="Order by fields. Must be an array ie. &order_by[name]=ASC&order_by[description]=DESC")
     * @QueryParam(name="filters", nullable=true, array=true, description="Filter by fields. Must be an array ie. &filters[id]=3")
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        try {
            $offset = $paramFetcher->get('offset');
            $limit = $paramFetcher->get('limit');
            $order_by = $paramFetcher->get('order_by');
            $filters = !is_null($paramFetcher->get('filters')) ? $paramFetcher->get('filters') : array();

            $em = $this->getDoctrine()->getManager();
            $entities = $em->getRepository('ApiBundle:Termbase')->findBy($filters, $order_by, $limit, $offset);
            if ($entities) {
                return $entities;
            }

            return FOSView::create('Not Found', Codes::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Create a Termbase entity.
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
        $entity = new Termbase();
        $form = $this->createForm(new TermbaseType(), $entity, array("method" => $request->getMethod()));
        $this->removeExtraFields($request, $form);
        $form->handleRequest($request);

        if ($form->isValid()) {
			if ($this->validateJSON($entity))
			{
				$em = $this->getDoctrine()->getManager();
				try{
					$em->persist($entity);
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
				return FOSView::create($this->getErrors(), Codes::HTTP_BAD_REQUEST );
			}
        }

        return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
    }
    /**
     * Update a Termbase entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function putAction(Request $request, Termbase $entity)
    {
        try {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(new TermbaseType(), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if ($form->isValid()) {
				if ($this->validateJSON($entity))
				{
					$em->flush();

					return array('updated'=>$entity->getId());
				}
				else
				{
					return FOSView::create($this->getErrors(), Codes::HTTP_BAD_REQUEST );
				}
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e) {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Partial Update to a Termbase entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function patchAction(Request $request, Termbase $entity)
    {
        return $this->putAction($request, $entity);
    }
    /**
     * Delete a Termbase entity.
     *
     * @View(statusCode=204)
     *
     * @param Request $request
     * @param $entity
     *
     * @return Response
     */
    public function deleteAction(Request $request, Termbase $entity)
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
	
	/**
     * Import a Termbase entity.
     * 
	 * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     *
     * @return Response
     */
    public function importAction(Request $request)
    {
		$termbase = new Termbase();
		$form = $this->createForm(new TermbaseType(), $termbase, array('action' => $this->generateUrl('termbase_import')))
				->remove('working_language')->add('file', 'file', array(
					'mapped'=>false
				))->add('import', 'submit');
		$form->handleRequest($request);
		
		if (($form->isSubmitted() && $form->isValid()) || $request->isMethod("POST"))
		{	
			$tbx_file = $request->files->get('file');
			$name = $request->get('name');
			if (!isset($tbx_file)) { return FOSView::create("No TBX file uploaded.", Codes::HTTP_BAD_REQUEST);}
			if (!isset($name)) {return FOSView::create("No termbase name provided.", Codes::HTTP_BAD_REQUEST);}
			
			if(!$this->validateTBX($tbx_file))
			{ return FOSView::create($this->getErrors(), Codes::HTTP_BAD_REQUEST ); }
			else
			{
				$em = $this->getDoctrine()->getManager();
				
				$rtc = new RawTBXConverter();
				$data = $rtc->convert($tbx_file);
				
				$termbase->setWorkingLanguage($data['working_language']);
				$em->persist($termbase);
				
				foreach($data['people'] as $person)
				{
					$p = new \ApiBundle\Entity\Person();
					$p->setTermbase($termbase->getId());
					$em->persist($p);
							
					$person['target'] = $p->getId();
					$p->setData(json_encode($person));
				}
				foreach($data["entries"] as $entry)
				{
					$e = new \ApiBundle\Entity\Entry();
					$e->setTermbase($termbase);
					$em->persist($e);
					$entry['id'] = $e->getId();
					$e->setData(json_encode($entry));
				}
				$em->flush();
				
				return array("created"=>$termbase->getId());
			}
		}

		return $this->render("@ApiBundle/Resources/views/default/import_termbase.html.twig", array('form'=>$form->createView()));
    }
	
	/**
     * Export a Termbase entity.
     *
     * @View(serializerEnableMaxDepthChecks=true)
     *
     * @param Request $request
     * @param Termbase $entity
     *
     * @return Response
     */
    public function exportAction(Request $request, Termbase $entity)
    {
		$data = array(
			"people" => [],
			"entries" => []
		);
		$data["working_language"] = $entity->getWorkingLanguage();
		
		foreach ($entity->getEntries() as $entry)
		{
			$entry_data = json_decode($entry->getData(), true);
			assert($entry->getId() == $entry_data['id'], "Corrupted entry id. Please contact an administrator.");
			array_push($data['entries'], $entry_data);
		}
		
		foreach ($entity->getPeople() as $person)
		{
			$person_data = json_decode($person->getData(), true);
			assert($person->getId() == $person_data['id'], "Corrupted entry id. Please contact an administrator.");
			array_push($data['people'], $person_data);
		}
		
        return $this->render("@ApiBundle/Resources/views/templates/tbx-basic.html.twig",$data);
    }
}
