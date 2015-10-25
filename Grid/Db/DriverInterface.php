<?php
namespace Grid\Db;

interface DriverInterface
{
	public function open();
	
	public function close();
	
	public function query($sql);
	
	public function fetch($sql);
	
	public function fetchAll($sql);
	
	public function insertId();
	
	public function affectedRows();
	
	public function numRows($result);
}