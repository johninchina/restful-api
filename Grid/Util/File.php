<?php
namespace Grid\Util;

class File
{
	// 循环创建目录
	public static function mkdir($path)
	{
		if (!file_exists($path)) {
			self::mkdir(dirname($path));
			mkdir($path);
		}
	}
	
	// 循环遍历目录
	public static function scandir($path, &$files = array())
	{
		if (is_dir($path)) {
			$handle = opendir($path);
			while (($file = readdir($handle)) !== false) {
				if ($file != '.' && $file != '..') {
					$real = rtrim($path, '/') . '/' . $file;
					if (is_file($real)) {
						$files[$file] = $file;
					}
					if (is_dir($real)) {
						self::scandir($real, &$files[$file]);
					}
				}
			}
			closedir($handle);
		}
		return $files;
	}
}