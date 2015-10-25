<?php
namespace Grid;

class Autoload
{
	public static $dirs = array();
	
	// 注册自动加载的搜索路径
	public static function register($dir)
	{
		if (!in_array($dir, self::$dirs)) {
			array_push(self::$dirs, $dir);
			spl_autoload_register(array(__CLASS__, 'loadClass'));
		}
	}
	
	// 自动加载实现
	public static function loadClass($className)
	{
		foreach (self::$dirs as $dir) {
			$dir = rtrim($dir, '/');
			$fileName = str_replace('\\', '/', $className);
			$classFile = $dir . '/' . $fileName . '.php';
			if (file_exists($classFile)) {
				require $classFile;
				return class_exists($className) || interface_exists($className);
			}
		}
		return false;
	}
}