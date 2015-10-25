<?php
namespace Resource;

use Grid\Http\Resource;
use Grid\Util\Xml;
use Grid\Exception\NotFound;
use Grid\Exception\BadRequest;

class Interfaces extends Resource
{
	protected $interfaces = array();
	
	public function init()
	{
		$this->interfaces = array();
	}
	

	 // /interface.json(.xml) 获取所有端口
	 // /interface/i1.json(.xml) 获取指定端口
	public function actionGet()
	{
		$name = $this->getParam(0);
		if ($name == '')
			return array('interfaces' => $this->interfaces);
		if (!$this->has($name, $interface))
			throw new NotFound();
		return array('interface' => $interface);
	}
	
	// /interface/i1.json(.xml) 修改指定端口
	public function actionPut()
	{
		return $this->success();
	}
}