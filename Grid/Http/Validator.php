<?php
namespace Grid\Http;

use Grid\Manager;

abstract class Validator
{
	protected $manager;
	
	public function __construct(Manager $manager, Array $options)
	{
		$this->manager = $manager;
		$this->setOptions($options);
	}
	
	public function setOptions(Array $options)
	{
		foreach ($options as $key=>$value) {
			$this->setOption($key, $value);
		}
	}
	
	public function setOption($key, $value)
	{
		$this->$key = $value;
	}
	
	public function getManager()
	{
		return $this->manager;
	}
	
	abstract function validate();
}