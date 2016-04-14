<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ApiBundle\Converter;

/**
 * Description of RawTBXConverter
 *
 * This Class is used to convert raw xml TBX-Basic into an object ready to be
 * persisted in the Database.
 * 
 * @author James Hayes <james.s.hayes@gmail.com>
 */
class RawTBXConverter
{
	protected $data = array(
		"people"=> array(),
		"entries"=> array(),
		"working_language"=>""
	);
	
	protected $key_list = array(
			"administrativeStatus"=>"status",
			"context"=>"contexts",
			"crossReference"=>"references",
			"customerSubset"=>"customers",
			"date"=>"date",
			"definition"=>"definitions",
			"externalCrossReference"=>"external_references",
			"geographicalUsage"=>"geo",
			"grammaticalGender"=>"gender",
			"langSet"=>"languages",
			"note"=>"notes",
			"partOfSpeech"=>"pos",
			"projectSubset"=>"projects",
			"source"=>"source",
			"target"=>"target",
			"term"=>"term",
			"termLocation"=>"locations",
			"termType"=>"type",
			"tig"=>"terms",
			"transac"=>"type",
			"transacGrp"=>"transactions",
			"xGraphic"=>"images"
				);
	
	/**
	 * 
	 * @param string $key_determiner
	 * 
	 * $key_determiner can be a nodeName or an attribute value
	 * use switch_descripGrp for descripGrp
	 * 
	 * @return string
	 */
	protected function getKey($key_determiner)
	{
		return $this->key_list[$key_determiner];
	}
	
	/**
	 * 
	 * @param \ArrayObject $data
	 * @param \ArrayObject $object
	 * @param string $key
	 */
	protected function updateData(\ArrayObject &$data, \ArrayObject &$object, $key)
	{
		(array_key_exists($key, $data)) ? 
			array_push($data[$key], $object) : $data[$key] = array($object);
	}
	
	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 */
	protected function switch_attribute_type(\DOMNode &$dom, \ArrayObject &$data)
	{
		if ($dom->parentNode->localName != "tig")
		{
			switch($dom->getAttribute('type')):
				case "subjectField":
					$data['subjectField'] = $dom->nodeValue;
					break;
				case "definition":
					$this->parse_descrip_definition($dom, $data);
					break;
				default:
					$this->parse_content_target($dom, $data);
			endswitch;
		}
		else
		{
			$this->parse_value($dom, $data, $this->getKey($dom->getAttribute('type')));
		}
	}
	
	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 */
	protected function switch_descripGrp(\DOMNode &$dom, \ArrayObject &$data)
	{
		switch($dom->parentNode->localName):
			case "termEntry":
				$this->parse_content_source($dom, $data, $this->getKey("definition"));
				break;
			case "tig":
				$this->parse_content_source($dom, $data, $this->getKey("context"));
		endswitch;
	}

	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 */
	protected function switch_localName(\DOMNode &$dom, \ArrayObject &$data)
	{
		switch($dom->localName):
			case "descripGrp":
				$this->switch_descripGrp($dom, $data);
				break;
			case "langSet":
				$langSet = array('code'=>$dom->getAttribute('xml:lang'));
				$this->append_level($dom, $data, $langSet);
				break;
			case "tig":
				$tig = array();
				$this->append_level($dom, $data, $tig);
				break;
			case "transacGrp":
				$this->parse_transac($dom, $data);
				break;
			case "transacNote":
				$this->parse_person_target($dom, $data);
				break;
			default:
				$this->parse_value($dom, $data);
		endswitch;
	}

	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 * @param string $key
	 */
	protected function parse_descrip_definition(\DOMNode &$dom, \ArrayObject &$data)
	{
		$object = array('content'=>$dom->nodeValue);
		$key = "definitions";
		$this->updateData($data, $object, $key);
	}

	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 * @param string $key
	 */
	protected function parse_content_source(\DOMNode &$dom, \ArrayObject &$data, $key = null)
	{
		$object = array();
		if ($key == null) {$key = $this->getKey($dom->getAttribute('type'));}
		
		foreach($dom->childNodes as $child)
		{
			if($child->nodeType == \XMLReader::ELEMENT && $child->localName == 'descrip')
			{	
				$object['content'] = $child->nodeValue;
			}
			else if ($child->nodeType == \XMLReader::ELEMENT && $child->localName == 'admin')
			{
				$object['source'] = $child->nodeValue;
			}
		}
		$this->updateData($data, $object, $key);
	}

	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 */
	protected function parse_content_target(\DOMNode &$dom, \ArrayObject &$data)
	{
		$object = array();
		$key = $this->getKey($dom->getAttribute('type'));
		
		foreach($dom->childNodes as $child)
		{
			if($child->hasAttributes() && $child->getAttribute('type') == "content")
			{
				$object['content'] = $child->nodeValue;
			}
			if($child->hasAttributes() && null !== $child->getAttribute('type') && $child->getAttribute('type') == "target")
			{
				$object['target'] = $child->nodeValue;
			}
		}
		$this->updateData($data, $object, $key);
	}

	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 */
	protected function parse_person_target(\DOMNode &$dom, \ArrayObject &$data)
	{
		if($dom->hasAttributes() && null !== $dom->getAttribute('target') && $dom->getAttribute('target') != '')
		{
			$data['person'] = $dom->nodeValue;
			$data['target'] = $dom->getAttribute('target');
		}
	}

	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 */
	protected function parse_transac(\DOMNode &$dom, \ArrayObject &$data)
	{
		$transac = array();
		$key = $this->getKey($dom->localName);
		
		foreach($dom->childNodes as $child)
		{
			if($child->nodeType == \XMLReader::ELEMENT)
			{
				$this->switch_localName($child, $transac);
			}
		}
		$this->updateData($data, $transac, $key);
	}

	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $data
	 * @param string $key
	 */
	protected function parse_value(\DOMNode &$dom, \ArrayObject &$data, $key = null)
	{
		$object = $dom->nodeValue;
		if($key == null){$key = $this->getKey($dom->localName);}
		$data[$key] = $object;
	}
	
	protected function append_level(\DOMNode &$dom, \ArrayObject &$parent, \ArrayObject &$child)
	{
		$key = $this->getKey($dom->localName);
		$this->parse_level($dom, $child);
		$this->updateData($parent, $child, $key);
	}
	
	/**
	 * 
	 * @param \DOMNode $dom
	 * @param \ArrayObject $entry
	 */
	protected function parse_level(\DOMNode &$dom, \ArrayObject &$data)
	{
		foreach($dom->childNodes as $child)
		{
			if($child->hasAttributes() && null !== $child->getAttribute('type') && $child->getAttribute('type') != '')
			{
				$this->switch_attribute_type($child, $data);
			}
			else if($child->nodeType == \XMLReader::ELEMENT)
			{
				$this->switch_localName($child, $data);
			}
		}
	}

	/**
	 * 
	 * @param string $tbx_file
	 * @return \ArrayObject
	 */
	public function convert($tbx_file)
	{
		$xml = new \XMLReader();
		$xml->open($tbx_file);
		
		while($xml->read())
		{
			if ($xml->nodeType == \XMLReader::ELEMENT && $xml->localName == "martif")
			{
				$this->data['working_language'] = $xml->getAttribute("xml:lang");
			}
			
			if($xml->nodeType == \XMLReader::ELEMENT && $xml->localName == "termEntry")
			{
				$entry = array(
					'id'=> $xml->getAttribute("id")
				);
				
				$dom = $xml->expand();
				$this->parse_level($dom, $entry);
				array_push($this->data['entries'], $entry);
			}
		}
		return $this->data;
	}
}
