<?php
namespace Validator;

use Grid\Http\Validator;
use Grid\Exception\Forbidden;

class Rbac extends Validator
{
	/*
	 * 0:隐藏
	 * 1:只读
	 * 2:读写
	 */
	protected $except = array();
	protected $mapping = array();
	
	public function validate()
	{
		// 用户admin不需要判断
		if ($_SESSION['username'] != 'admin') {
			// $except中指定的资源不需要认证
			$rName = $this->getManager()->getRequest()->getResource();
			if (!in_array($rName, $this->except)) {
				// 获取权限名称
				$pName = isset($this->mapping[$rName]) ? $this->mapping[$rName] : strtolower($rName);
				$permission = $_SESSION['permission'];
				// 判断是否具有资源权限
				if (!isset($permission[$pName]))
					throw new Forbidden();
				switch ($permission[$pName]) {
					case 0:
						$allowMethods = array();
						break;
					case 1:
						$allowMethods = array('GET');
						break;
					case 2:
						$allowMethods = array('GET', 'POST', 'PUT', 'DELETE');
						break;
				}
				// 判断是否具有操作权限
				$mName = $this->getManager()->getRequest()->getMethod();
				if (!in_array($mName, $allowMethods))
					throw new Forbidden();
			}
		}
	}
}