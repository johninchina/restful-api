<?php
namespace Grid\Util;

class Xml
{
	protected $xml;
	protected $data;
	
	public function __construct($data)
	{
		$this->xml = new \DOMDocument('1.0', 'UTF-8');
		$this->data = $data;
	}

	public function getValidTag($tag, $default)
	{
		$pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
		if (preg_match($pattern, $tag))
			return $tag;
		elseif (is_numeric($tag))
			return $default;
	}
	
	protected function createNode($nodeName, $data)
	{
		$node = $this->xml->createElement($nodeName);
		$subNodeName = rtrim($nodeName, 's');
		if(is_array($data)){
			if(isset($data['@attributes'])) {
				foreach($data['@attributes'] as $key => $value) {
					$tag = $this->getValidTag($key, $subNodeName);
					$node->setAttribute($tag, $value === true ? 'true' : $value);
				}
				unset($data['@attributes']);
			}
			if(isset($data['@value'])) {
				$node->appendChild($this->xml->createTextNode($data['@value'] === true ? 'true' : $data['@value']));
				unset($data['@value']);
				return $node;
			} else if(isset($data['@cdata'])) {
				$node->appendChild($this->xml->createCDATASection($data['@cdata'] === true ? 'true' : $data['@cdata']));
				unset($data['@cdata']);
				return $node;
			}
		}
		if(is_array($data)){
			foreach($data as $key=>$value){
				$tag = $this->getValidTag($key, $subNodeName);
				if(is_array($value) && is_numeric(key($value))) {
					foreach($value as $k=>$v){
						$node->appendChild($this->createNode($tag, $v));
					}
				} else {
					$node->appendChild($this->createNode($tag, $value));
				}
				unset($data[$key]);
			}
		}
		if(!is_array($data)) {
			$node->appendChild($this->xml->createTextNode($data === true ? 'true' : $data));
		}
		return $node;
	}
	
	public function __toString()
	{
		try {
			$rootTag = $this->getValidTag(key($this->data));
			$node = $this->createNode($rootTag, $this->data[$rootTag]);
			$this->xml->appendChild($node);
			$xml = $this->xml->saveXML();
			$this->xml = null;
			return $xml;
		} catch (\Exception $e) {
			return (string)new self($e->getBody());
		}
	}
	
	public static function toArray($xmlString)
	{
		$xml = new \DOMDocument();
		if ($xmlString == '')
			return array();
		elseif (is_file($xmlString) && file_exists($xmlString))
			$loaded = $xml->load($xmlString);
		else
			$loaded = $xml->loadXML($xmlString);
		if (!$loaded) throw new \Exception();
		$array[$xml->documentElement->tagName] = self::createArray($xml->documentElement);
		$xml = null;
		return $array;
	}
	
	public static function createArray($node)
	{
		$output = array();
		switch ($node->nodeType) {
			case XML_CDATA_SECTION_NODE:
				$output['@cdata'] = trim($node->textContent);
				break;
			case XML_TEXT_NODE:
				$output = trim($node->textContent);
				break;
			case XML_ELEMENT_NODE:
				for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
					$child = $node->childNodes->item($i);
					$v = self::createArray($child);
					if(isset($child->tagName)) {
						$t = $child->tagName;
						if(!isset($output[$t]))
							$output[$t] = array();
						$output[$t][] = $v;
					} else {
						if($v !== '')
							$output = $v;
					}
				}
				if(is_array($output)) {
					foreach ($output as $t => $v) {
						if(is_array($v) && count($v)==1)
							$output[$t] = $v[0];
					}
					if(empty($output))
						$output = '';
				}
				if($node->attributes->length) {
					$a = array();
					foreach($node->attributes as $attrName => $attrNode) {
						$a[$attrName] = (string) $attrNode->value;
					}
					if(!is_array($output))
						$output = array('@value' => $output);
					$output['@attributes'] = $a;
				}
				break;
		}
		return $output;
	}
	
	public static function getOption($array, $option, $default = null)
	{
		if (isset($array[$option]))
			return $array[$option];
		elseif (isset($array['@attributes'][$option]))
			return $array['@attributes'][$option];
		else 
			return $default;
	}
}