<?php
namespace Grid;

use Grid\Http\Exception;
use Grid\Exception\NotFound;
use Grid\Http\Request;
use Grid\Http\Response;

class Manager
{
	protected $application	= null;
	protected $request		= null;
	protected $response		= null;
	protected $config		= array();
	protected $components	= array();
	protected $resources	= array();
	protected $validators	= array();
	
	public function __construct($config = array())
	{
		$this->config = $config;
	}
	
	public function getConfig($option = null, $default = array())
	{
		if ($option != null) {
			if (is_string($option)) {
				$option = trim($option);
				if (isset($this->config[$option])) {
					return $this->config[$option];
				}
				return $default;
			}
			throw new Exception();
		}
		return $this->config;
	}
	
	// 获取Application实例
	// Application为单例模式
	public function getApplication()
	{
		if ($this->application == null) 
			$this->application = new Application($this);
		return $this->application;
	}
	
	// 获取指定名称的组件
	// 组件名称区分大小写
	// 组件必须在config中配置
	// 组件类必须实现Component接口
	public function getComponent($name, $new = false)
	{
		if (!is_string($name)) 
			throw new Exception();
		$componentsConfig = $this->getConfig('component');
		if (!isset($componentsConfig[$name]))
			throw new Exception();
		$componentConfig = $componentsConfig[$name];
		if (!$new || !isset($this->components[$name])) {
			$componentClass = $componentConfig['class'];
			if (!class_exists($componentClass) || !is_subclass_of($componentClass, 'Grid\Http\Component'))
				throw new Exception();
			$componentOptions = isset($componentConfig['options']) ? $componentConfig['options'] : null;
			$component = $componentClass::createComponent($componentOptions);
			if (!$new)
				$this->components[$name] = $component;
			else 
				return $component;
		}
		return $this->components[$name];
	}
	
	// 获取指定名称的资源
	// 资源名区分大小写
	// 资源必须继承Resource抽象类
	public function getResource($name)
	{
		if (!is_string($name))
			throw new Exception();
		if (!isset($this->resources[$name])) {
			$resourceConfig = $this->getConfig('resource');
			if (isset($resourceConfig['aliases']) && isset($resourceConfig['aliases'][$name]))
				$name = $resourceConfig['aliases'][$name];
			$resourceClass = 'Resource\\' . $name;
			if (!class_exists($resourceClass) || !is_subclass_of($resourceClass, 'Grid\Http\Resource'))
				throw new NotFound();
			$resource = new $resourceClass($this);
			$this->resources[$name] = $resource;
		}
		return $this->resources[$name];
	}
	
	// 获取所有的校验器
	// 校验器必须在config中配置
	// 校验器必须继承Validator抽象类
	public function getValidators()
	{
		if (empty($this->validators)) {
			$validatorConfig = $this->getConfig('validator');
			foreach ($validatorConfig as $validatorClass=>$options) {
				$validatorClass = 'Validator\\' . $validatorClass;
				if (!class_exists($validatorClass) || !is_subclass_of($validatorClass, 'Grid\Http\Validator'))
					throw new Exception();
				$validator = new $validatorClass($this, $options);
				$this->validators[$validatorClass] = $validator;
			}
		}
		return $this->validators;
	}
	
	// 获取Request实例
	// Request为单例模式
	public function getRequest()
	{
		if ($this->request == null)
			$this->request = new Request();
		return $this->request;
	}
	
	// 获取Response实例
	// Response为单例模式
	public function getResponse()
	{
		if ($this->response == null)
			$this->response = new Response();
		return $this->response;
	}
}