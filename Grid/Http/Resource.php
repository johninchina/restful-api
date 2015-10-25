<?php
namespace Grid\Http;

use Grid\Manager;
use Grid\Exception\MethodNotAllowed;

abstract class Resource
{
	protected $manager;
	
	public function __construct(Manager $manager)
	{
		$this->manager	= $manager;
		$this->init();
	}
	
	// 初始化资源
	// 资源被实例化时自动执行该方法
	// 该方法可以在子类中被覆盖
	public function init(){}
	
	// 获取指定下标的参数
	public function getParam($offset)
	{
		$params = $this->getParams();
		$offset = intval($offset);
		$length = count($params);
		if ($length > 0 && $offset < $length)
			return $params[$offset];
		return '';
	}
	
	// 获取Data中指定下标的参数
	public function getData($name = '', $default = array())
	{
		$data = $this->getManager()->getRequest()->getData();
		if ($name !== '')
			return isset($data[$name]) ? $data[$name] : $default;
		return $data;
	}
	
	// 返回成功结果
	public function success($data = null)
	{
		$result['success'] = 'true';
		if (!is_null($data))
			$result['data'] = $data;
		return array('result' => $result);
	}
	
	// 调用资源的方法，执行请求操作
	final public function exec()
	{
		$method = $this->getManager()->getRequest()->getMethod();
		$method = 'action' . ucfirst(strtolower($method));
		if (!method_exists($this, $method))
			throw new MethodNotAllowed();
		return $this->$method();
	}
	
	public function getManager()
	{
		return $this->manager;
	}
	
	// 获取资源名
	public function getResource()
	{
		return $this->getManager()->getRequest()->getResource();
	}
	
	// 获取请求类型
	public function getMethod()
	{
		return $this->getManager()->getRequest()->getMethod();
	}
	
	// 获取所有参数
	public function getParams()
	{
		return $this->getManager()->getRequest()->getParams();
	}
}