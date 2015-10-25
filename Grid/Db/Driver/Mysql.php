<?php
namespace Grid\Db\Driver;

use Grid\Db\DriverInterface;

class Mysql implements DriverInterface
{
	private	  $link;
	protected $host;
	protected $port;
	protected $username;
	protected $password;
	protected $database;
	protected $charset;
	
	public function __construct(Array $options)
	{
		$this->setOptions($options);
	}
	
	public function setOptions(Array $options)
	{
		foreach ($options as $key=>$value)
			$this->setOption($key, $value);
	}
	
	public function setOption($key, $value)
	{
		$this->$key=$value;
	}
	
	public function open()
	{
		$this->link = @ mysql_connect($this->host . ':' . $this->port, $this->username, $this->password);
		if (!$this->link)
			throw new \Exception('Database connection failed.', 500);
		if (!mysql_select_db($this->database, $this->link))
			throw new \Exception('Database not exists.', 500);
		mysql_query("SET NAMES " . $this->charset, $this->link);
	}
	
	public function close()
	{
		mysql_close($this->link);
		$this->link = null;
	}
	
	public function query($sql)
	{
		$this->open();
		return mysql_query($sql, $this->link);
	}
	
	public function insertId()
	{
		$this->open();
		return mysql_insert_id($this->link);
	}
	
	public function affectedRows()
	{
		$this->open();
		return mysql_affected_rows($this->link);
	}
	
	public function numRows($result)
	{
		$this->open();
		return mysql_num_rows($result);
	}
	
	public function fetch($sql)
	{
		$result = $this->query($sql);
		if (mysql_num_rows($result)) {
			$row = mysql_fetch_assoc($result);
			mysql_free_result($result);
			return $row;
		}
		return null;
	}
	
	public function fetchAll($sql)
	{
		$result = $this->query($sql);
		if (mysql_num_rows($result)) {
			while ($row = mysql_fetch_assoc($result)) {
				$rows[] = $row;
			}
			mysql_free_result($result);
			return $rows;
		}
		return null;
	}
}