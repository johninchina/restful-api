<?php
namespace Grid;

use Grid\Http\Exception;

class Application
{
	protected $manager;
	protected $request;
	protected $response;
	
	public function __construct(Manager $manager)
	{
		$this->manager = $manager;
		// 初始化请求
		$this->request = $manager->getRequest();
		// 初始化响应
		$this->response= $manager->getResponse();
	}
	
	// 确保application为单例模式
	public static function init($config = array())
	{
		// 注册自动加载
		$basePath = dirname(__DIR__);
		include $basePath . '/Grid/Autoload.php';
		Autoload::register($basePath);
		// 配置系统
		$manager = new Manager($config);
		// 创建应用
		return $manager->getApplication();
	}
	
	// 启动application
	public function run()
	{
		try {
			// 请求过滤
			$this->validate();
			// 访问资源
			$resourceName		= $this->request->getResource();
			$resource			= $this->manager->getResource($resourceName);
			$options['body']	= $resource->exec();
			$options['code']	= 200;
		} catch (Exception $e) {
			$options['body']	= $e->getBody();
			$options['code']	= $e->getCode();
		}
		$options['type']		= $this->request->getResponseType();
		// 返回响应
		$this->response->setOptions($options)->output();
	}
	
	// 请求过滤
	public function validate()
	{
		$validators = $this->manager->getValidators();
		foreach ($validators as $validator) {
			$validator->validate();
		}
	}
}