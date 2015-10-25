<?php
namespace Grid\Http;

use Grid\Util\Json;
use Grid\Util\Xml;

class Response
{
	protected $type = 'json';
	protected $code = '200';
	protected $body = array();
	
	public function setOptions(Array $options)
	{
		foreach ($options as $key=>$value) {
			$this->setOption($key, $value);
		}
		return $this;
	}
	
	public function setOption($key, $value)
	{
		$this->$key = $value;
	}
	
	private function header()
	{
		$headers['Cache-Control'] = "no-cache, must-revalidate";
		$headers['Content-Type'] = "application/{$this->type}";
		foreach ($headers as $name => $value) {
			header($name . ': ' . $value, true, $this->code);
		}
	}
	
	private function body()
	{
		if ($this->type == 'json')
			$body = new Json($this->body);
		else
			$body = new Xml($this->body);
		return $body;
	}
	
	public function output()
	{
		$this->header();
		echo $this->body();
	}
}