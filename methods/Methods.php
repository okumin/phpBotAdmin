<?php
/**
 * Copyright (c) 2012 okumin, http://okumin.com/
 */
 
// DbMethods、FileMethodsに適用するインターフェース
interface Methods
{
	public function read($name, array $select = array(), array $search = array());
	public function insert($name, array $data);
	public function delete($name, array $search);
	public function overWrite($name, array $data);
	public function update($name, $data, array $search);
}