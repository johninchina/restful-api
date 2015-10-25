<?php
namespace Grid\Db;

use Grid\Http\Component;
use Grid\Http\Exception;

class DbFactory implements Component
{
	public static function createComponent($options)
	{
		$driver = isset($options['driver']) ? $options['driver'] : 'mysql';
		$driverName = ucfirst(strtolower($driver));
		$driverClass = 'Grid\Db\Driver\\' . $driverName;
		if (!class_exists($driverClass) || !is_subclass_of($driverClass, 'Grid\Db\DriverInterface'))
			throw new Exception();
		$driverInstance = new $driverClass($options);
		return $driverInstance;
	}
}