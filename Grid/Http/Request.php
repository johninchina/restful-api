<?php
namespace Grid\Http;

use Grid\Util\Json;
use Grid\Util\Xml;

class Request
{
	private $resource;
	private $method;
	private $params = array();
	private $data;
	private $responseType;
	private $hasSuffix;
	
	public function __construct()
	{
		$this->parseUri();
		$this->parseHeader();
		$this->parseData();
	}
	
	public function __get($name)
	{
		return $this->getHeader($name);
	}
	
	// 解析URI
	private function parseUri()
	{
		$uri = $this->getUri();
		$uri = ltrim($uri, '/');
		$parts = explode('.', $uri);
		// 获取返回类型
		$last = strtolower(array_pop($parts));
		if (in_array($last, array('json', 'xml'))) {
			$this->responseType = $last;
			$this->hasSuffix = true;
		} else {
			$this->responseType = 'json';
			$this->hasSuffix = false;
			array_push($parts, $last);
		}
		// 获取资源名称和参数
		$parts = join('.', $parts);
		$params = explode('/', $parts);
		$this->resource = ucfirst(array_shift($params));
		$this->params = $params;
	}
	
	// 解析Header
	private function parseHeader()
	{
		// 获取请求方式
		$method = $this->getHeader('requestMethod', 'GET');
		$this->method = strtoupper($method);
	}
	
	// 解析Data
	private function parseData()
	{
		if ($this->getHeader('contentLength') !== 0) {
			$contentType = $this->getContentType();
			$data = file_get_contents('php://input');
			if ($contentType == 'application/xml') {
				$data = Xml::toArray($data);
			} elseif ($contentType == 'application/json') {
				$data = Json::toArray($data);
			} else {
				$data = null;
			}
		} else {
			$data = null;
		}
		$this->data = $data;
	}
	
	// 获取完整URI
	private function getUri()
	{
		if (isset($_SERVER['REDIRECT_URL']) && isset($_SERVER['SCRIPT_NAME'])) {
			$dirname = dirname($_SERVER['SCRIPT_NAME']);
			$uri = substr($_SERVER['REDIRECT_URL'], strlen($dirname == DIRECTORY_SEPARATOR ? '' : $dirname));
		} elseif (isset($_SERVER['REQUEST_URI'])) {
			$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
		} elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['SCRIPT_NAME'])) {
			$uri = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']));
		}
		return $uri;
	}
	
	// 获取Header里的信息
	private function getHeader($name, $default = null)
	{
		$name = strtoupper(preg_replace('/([A-Z])/', '_$1', $name));
		if (isset($_SERVER['HTTP_'.$name])) {
			return $_SERVER['HTTP_'.$name];
		} elseif (isset($_SERVER[$name])) {
			return $_SERVER[$name];
		} else {
			return $default;
		}
	}
	
	// 获取提交的数据的类型
	private function getContentType()
	{
		$contentType = $this->getHeader('contentType');
		$parts = explode(';', $contentType);
		return $parts[0];
	}
	
	// 获取资源名
	public function getResource()
	{
		return $this->resource;
	}
	
	// 获取请求方式
	public function getMethod()
	{
		return $this->method;
	}
	
	// 获取参数
	public function getParams()
	{
		return $this->params;
	}
	
	// 获取Data
	public function getData()
	{
		return $this->data;
	}
	
	// 获取希望返回的类型
	public function getResponseType()
	{
		return $this->responseType;
	}
	
	// 判断是否包含后缀
	public function hasSuffix()
	{
		return $this->hasSuffix;
	}
}