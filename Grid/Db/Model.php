<?php
namespace Grid\Db;

use Grid\Db\DriverInterface;

abstract class Model
{
	private static $models;
	private $sql;
	private $data;
	protected $db;
	protected $tableName;
	
	protected function __construct(DriverInterface $db)
	{
		$this->db = $db;
	}
	
	public static function model(DriverInterface $db)
	{
		$className = get_called_class();
		if(!isset(self::$models[$className]))
		{
			$className = ucfirst($className);
			if (!class_exists($className) || !is_subclass_of($className, __CLASS__))
				throw new \Exception('INTERNAL_ERROR', 500);
			self::$models[$className]=new $className($db);
		}
		return self::$models[$className];
	}
	
	public function tableName()
	{
		if (!empty($this->tableName)) 
			return $this->tableName;
		$tableName = strtolower(get_class($this));
		if(($pos=strrpos($tableName,'\\')) !== false)
			$tableName = substr($tableName, $pos+1);
		$tableName = preg_replace('/([A-Z])/', '_$1', $tableName);
		return $tableName;
	}
	
	public function find()
	{
		return $this->db->fetch($this->parseSql());
	}
	
	public function findAll()
	{
		return $this->db->fetchAll($this->parseSql());
	}
	
	public function insert()
	{
		$fields = join(',', array_keys($this->data));
		$values = join(',', $this->data);
		$this->data = null;
		$sql = "INSERT INTO `" . $this->tableName() . "` ($fields) VALUES ($values)";
		$this->db->query($sql);
		return $this->db->insertId();
	}
	
	public function update($condition)
	{
		foreach ($this->data as $key=>$value) 
			$this->data[$key] = "$key=$value";
		$set = join(',', $this->data);
		$this->data = null;
		$sql = "UPDATE `" . $this->tableName() . "` SET $set WHERE $condition";
		return $this->db->query($sql);
	}
	
	public function delete($condition)
	{
		$sql = "DELETE FROM `" . $this->tableName() . "` WHERE $condition";
		return $this->db->query($sql);
	}
	
	public function count($condition)
	{
		$sql = "SELECT * FROM `" . $this->tableName() . "` WHERE $condition";
	}
	
	public function data($data = array())
	{
		$this->data = null;
		foreach ($data as $key=>$value) {
			$quoteKey = "`$key`";
			$quoteValue = "'$value'";
			$data[$quoteKey] = $quoteValue;
			unset($data[$key]);
		}
		$this->data = $data;
		return $this;
	}
	
	public function fields($fields)
	{
		if ($fields !== '*') {
			$fields = explode(',', $fields);
			foreach ($fields as $key=>$value) {
				$fields[$key] = "'" . trim($value) . "'";
			}
			$fields = join(',', $fields);
		}
		$this->sql['fields'] = $fields;
		return $this;
	}
	
	public function where($condition)
	{
		$this->sql['where'] = $condition;
		return $this;
	}
	
	public function limit($offset, $rows)
	{
		$this->sql['limit'] = array($offset, $rows);
		return $this;
	}
	
	public function order($field, $direction = 'DESC')
	{
		$this->sql['order'] = array("`$field`", $direction);
		return $this;
	}
	
	public function group($field)
	{
		$this->sql['group'] = "`$field`";
		return $this;
	}
	
	private function parseSql()
	{
		$sqlTpl = "SELECT%DISTINCT% %FIELD% %FROM%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT%";
		$sql = str_replace(
			array('%DISTINCT%', '%FIELD%', '%FROM%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%'),
			array(
				isset($this->sql['distinct']) ? ' DISTINCT ' . $this->sql['distinct'] : '',
				isset($this->sql['fields']) ? $this->sql['fields'] : '*',
				"FROM `" . $this->tableName() . "`",
				isset($this->sql['where']) ? ' WHERE ' . $this->sql['where'] : '',
				isset($this->sql['group']) ? ' GROUP BY ' . $this->sql['group'] : '',
				isset($this->sql['having']) ? 'HAVING ' . $this->sql['having'] : '',
				isset($this->sql['order']) ? ' ORDER BY ' . $this->sql['order'][0] . " " . $this->sql['order'][1] : '',
				isset($this->sql['limit']) ? ' LIMIT ' . $this->sql['limit'][0] . "," . $this->sql['limit'][1] : ''
			),$sqlTpl);
		$this->sql = null;
		return $sql;
	}
}