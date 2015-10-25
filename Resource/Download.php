<?php
namespace Resource;

use Grid\Http\Resource;
use Grid\Exception\NotFound;

class Download extends Resource
{
	protected $typePaths = array(
		'doc'		=> '/SmartGrid/documents/',
		'log'		=> '/var/log/',
		'license'	=> '/SmartGrid/',
		'config'	=> '/SmartGrid/tmp/_upload_from_apache/config/',
		'rulelog'	=> '/SmartGrid/smartl7/var/log/smartl7/',
		'default'	=> '/SmartGrid/tmp/'
	);
	
	// /download/test.txt 下载/SmartGrid/tmp/test.txt
	// /download/doc/test.txt 下载/SmartGrid/documents/test.txt
	public function actionGet()
	{
		$first = $this->getParam(0);
		$second = $this->getParam(1);
		if ($first == '')
			throw new NotFound();
		if ($second == '') {
			$type = 'default';
			$file = $first;
		} else {
			$type = $first;
			$file = $second;
		}
		// 判断类型是否正确
		if (!isset($this->typePaths[$type]))
			throw new NotFound();
		$dir = $this->typePaths[$type];
		// 判断是否是xml或者json文件
		if ($this->getManager()->getRequest()->hasSuffix())
			$file .= '.' . $this->getManager()->getRequest()->getResponseType();
		$real = $dir . $file;
		// 判断文件是否存在
		if (!file_exists($real))
			throw new NotFound();
		$agent = $_SERVER["HTTP_USER_AGENT"];
		if (preg_match('/MSIE/', $agent)) {
			header('Content-Disposition: attachment; filename="' . rawurlencode($file) . '"');
		} elseif (preg_match('/Firefox/', $agent)) {
			header ('Content-Disposition: attachment; filename*="utf8\'\'' . $file . '"');
		} else {
			header('Content-Disposition: attachment; filename="' . $file . '"');
		}
		header('Content-Length:' . filesize($real));
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/octet-stream');
		header("Content-Type: application/force-download");
		header("Content-Type: application/download");
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Pragma: public');
		header('Expires: 0');
		ob_end_clean();
		flush();
		@readfile($real);
		exit();
	}
}