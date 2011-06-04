<?php

class base_data_util extends util
{
	var $__tableName;
	var $__idField;

	function base_data_util($object=null)
	{
		if(!$this->__tableName) {
			$this->__tableName = get_class($this);
		}
		if(!$this->__idField) {
			$this->__idField = get_class($this)."Id";
		}

		if($object) {
			foreach(get_object_vars($object) as $variable=>$value) {
				$this->{$variable} = $value;
			}
		}
	}

	function load($id)
	{
		foreach(get_object_vars($this->find($id)) as $variable=>$value) {
			$this->{$variable} = $value;
		}
	}

	function add($data=array(), $return_id=true)
	{
		foreach($data as $key=>$val) {
			if(!is_numeric($val)) {
				$data[$key] = "'".addslashes($val)."'";
			}
		}

		$fields	= '`'.implode('`, `', array_keys($data)).'`';
		$values	= implode(', ', $data);

		$sql = "insert into `$this->__tableName`($fields) values($values)";

		return $this->query($sql, $return_id);
	}

	function update($data=array(), $return_id=false)
	{
		$values	= array();
		foreach($data as $key=>$val) {
			if($key != $this->__idField) {
				if(!is_numeric($val) && $val != 'now()') {
					$val	= "'".addslashes($val)."'";
				}
				$values[]	= "`$key`=$val";
			}
		}
		$values	= implode(', ', $values);

		$sql = "update `$this->__tableName` set $values where `$this->__idField` = ".$data[$this->__idField];

		return $this->query($sql, $return_id);
	}

	function delete($id=0)
	{
		if(is_numeric($id) && intval($id) > 0) {
			$sql = "delete from `$this->__tableName` where `$this->__idField`=".intval($id);
			return $this->query($sql);
		}
		return false;
	}

	function getWhereArray($fields=array())
	{
		$where = array();
		foreach($fields as $key=>$val) {
			if(is_null($val)) {
				$where[] = "`$key` is null";
			}
			elseif(is_numeric($val)) {
				$where[] = "`$key`=$val";
			}
			else {
				$where[] = "`$key`='$val'";
			}
		}

		return $where;
	}

	function getSqlWhere($fields=array())
	{
		$where = $this->getWhereArray($fields);

		$sql = "select * from `$this->__tableName`";
		if(count($where) > 0) {
			$sql .= " where ".implode(' and ', $where);
		}

		return $sql;
	}

	function find($id=0)
	{
		if(!$this->__tableName) {
			$this->data_util();
		}

		$id = intval($id);

		$sql = $this->getSqlWhere(array($this->__idField=>$id));

		return $this->getOne($sql);
	}

	function findAll($orderBy=null, $limit=0, $fields=array())
	{
		$sql = $this->getSqlWhere($fields);
		if($orderBy) {
			$sql .= " order by $orderBy";
		}
		if($limit > 0) {
			$sql .= " limit $limit";
		}

		return (array)$this->get($sql);
	}

	function findOneBy($fields=array())
	{
		return $this->findBy($fields, true);
	}

	function findBy($fields=array(), $single=false)
	{
		if(!$this->__tableName) {
			$this->data_util();
		}

		$sql = $this->getSqlWhere($fields);

		if($single) {
			return $this->getOne($sql);
		}
		else {
			return $this->get($sql);
		}
	}

	function getRandom()
	{
		return array_shift($this->findAll('rand() desc', 1));
	}

	function getOne($sql=null)
	{
		global $dm;
		if($sql) {
			return $dm->db()->get_row($sql);
		}
	}

	function get($sql=null)
	{
		global $dm;
		if($sql) {
			return $dm->db()->get_results($sql);
		}
	}

	function query($sql=null, $return_id=false)
	{
		global $dm;
		if($sql) {
			$result = $dm->db()->query($sql);
			if($return_id) {
				return $dm->db()->insert_id;
			}
			else {
				return $result;
			}
		}
	}
}

