<?php

class base_data_util extends util
{
	var $__tableName;
	var $__idField;

	public function base_data_util($object=null)
	{
		if(!$this->__tableName) {
			$this->__tableName = get_class($this);
		}
		if(!$this->__idField) {
			$this->__idField = get_class($this)."Id";
		}

		if($object) {
			$this->loadObject($object);
		}
	}

	// Load a row into this object
	public function load($id)
	{
		$this->loadObject($this->find($id));
	}

	// Load an object
	public function loadObject($object)
	{
		foreach(get_object_vars($object) as $variable=>$value) {
			$this->{$variable} = $value;
		}
	}

	// Add a row to the database table
	public function add($data=array(), $return_id=true)
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

	// Update a row in the database table - id field must be supplied in $data
	public function update($data=array(), $return_id=false)
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

	// Delete a row from the database table
	public function delete($id=0)
	{
		if(is_numeric($id) && intval($id) > 0) {
			$sql = "delete from `$this->__tableName` where `$this->__idField`=".intval($id);
			return $this->query($sql);
		}
		return false;
	}

	// Retrieve one row from the database table by id
	public function find($id=0)
	{
		$id = intval($id);

		$options = array('where'=>array($this->__idField=>$id));
		return $this->findOneBy($options);
	}

	// Retrieve one row from the database table by a list of fields
	public function findOneBy($options=array())
	{
		$options['limit'] = 1;
		return $this->findBy($options);
	}

	// Retrieve multiple rows from the database table, with the option to specific fields, a limit, and an order by clause
	public function findBy($options=array())
	{
		extract($options);

		if(!isset($fields))	$fields = array();
		if(!isset($joins))	$joins = array();
		if(!isset($where))	$where = array();
		if(!isset($group))	$group = array();
		if(!isset($order))	$order = array();
		if(!is_array($fields))	$fields = array($fields);
		if(!is_array($joins))	$joins = array($joins);
		if(!is_array($where))	$where = array($where);
		if(!is_array($group))	$group = array($group);
		if(!is_array($order))	$order = array($order);
		if(!isset($limit))	$limit = 0;
		$sql = $this->getSqlQuery($fields, $joins, $where, $group, $order, $limit);

		if($limit == 1) {
			return $this->getOne($sql);
		}
		return (array)$this->get($sql);
	}

	// Retrieve one random record from the data
	public function getRandom()
	{
		$options = array('order'=>array('rand() desc'));
		return $this->findOneBy($options);
	}



	/** 
	 * The following functions are mainly used by this class internally. Only use them if you have a specific query to
	 * run that cannot be created by the function above.
	 **/

	// Retrieve one row from an SQL statement
	public function getOne($sql=null)
	{
		global $dm;
		if($sql) {
			return $dm->db()->getRow($sql);
		}
	}

	// Retrieve the results from an SQL statement
	public function get($sql=null)
	{
		global $dm;
		if($sql) {
			return $dm->db()->getResults($sql);
		}
	}

	// Run an SQL query
	public function query($sql=null, $return_id=false)
	{
		global $dm;
		if($sql) {
			$result = $dm->db()->query($sql);
			if($return_id) {
				return $dm->db()->getLastInsertId();
			}
			else {
				return $result;
			}
		}
	}



	/**
	 * The following functions are all private data_util functions and cannot be called outside this class.
	 **/

	private function getSqlQuery($fields=array(), $joins=array(), $where=array(), $group=array(), $order=array(), $limit=0)
	{
		if(!is_array($fields))	$fields = array($fields);
		if(!is_array($joins))	$joins = array($joins);
		if(!is_array($where))	$where = array($where);
		if(!is_array($group))	$group = array($group);
		if(!is_array($order))	$order = array($order);

		$sql  = $this->getSqlSelect($fields);
		$sql .= $this->getSqlJoins($joins);
		$sql .= $this->getSqlWhere($where);
		$sql .= $this->getSqlGroup($group);
		$sql .= $this->getSqlOrder($order);
		$sql .= $this->getSqlLimit($limit);

		return $sql;
	}

	// Get the SELECT clause of a query
	private function getSqlSelect($fields=array())
	{
		if($fields) {
			$sql = "select ".implode(', ', $fields)." from `$this->__tableName` a";
		}
		else {
			$sql = "select * from `$this->__tableName` a";
		}

		return $sql;
	}

	// Get the JOIN clause of a query
	private function getSqlJoins($joins=array())
	{
		$sql = '';

		if($joins) {
			foreach($joins as $j) {
				if(isset($j['tableName'])) {
					$tableName = $j['tableName'];
					if(preg_match('/##[A-Za-z0-9]*##/', $tableName)) {
						global $dm;
						$class = ltrim(trim($tableName, '#'), '#');
						$tableName = $dm->{$class}->__tableName;
					}
				}
				else {
					continue;
				}
				$type	= isset($j['type']) ? $j['type'] : 'left';
				$alias	= isset($j['alias']) ? $j['alias'] : substr($tableName, 0, 1);
				$use	= isset($j['use']) ? $j['use'] : '';
				if($use) {
					$sql .= " $type join `$tableName` $alias on $use[0]=$use[1]";
				}
			}
		}

		return $sql;
	}

	// Get the WHERE clause of a query
	private function getSqlWhere($fields=array())
	{
		$sql = '';

		$where = array();
		foreach($fields as $key=>$val) {
			if(strpos($key, '.') === false) {
				$key = "`$key`";
			}
			if(is_null($val)) {
				$where[] = "$key is null";
			}
			elseif(is_numeric($val)) {
				$where[] = "$key=$val";
			}
			else {
				$where[] = "$key='$val'";
			}
		}

		if(count($where) > 0) {
			$sql .= " where ".implode(' and ', $where);
		}

		return $sql;
	}

	// Get the GROUP BY clause of a query
	private function getSqlGroup($group=array())
	{
		$sql = '';

		if(count($group) > 0) {
			$sql .= " group by ".implode(', ', $group);
		}

		return $sql;
	}

	// Get the ORDER BY clause of a query
	private function getSqlOrder($order=array())
	{
		$sql = '';

		if(count($order) > 0) {
			$sql .= " order by ".implode(', ', $order);
		}

		return $sql;
	}

	// Get the LIMIT clause of a query
	private function getSqlLimit($limit=0)
	{
		$sql = '';

		if($limit) {
			$sql .= " limit $limit";
		}

		return $sql;
	}

	// Dynamic function to run a query.
	//
	// Usage: $obj->findOneByUsername($username);
	// The above command will run a select query where (`username` = $username) and limit the results to 1 record.
	//
	// Usage: $obj->findByActive(1);
	// The above command will run a select query where (`active` = 1) and return all records.
	private function dynamicFind($name, $args)
	{
		$results = false;
		if(substr($name, 0, 4) == 'find') {
			$end = strpos($name, 'By');
			$func = substr($name, 0, $end + 2);
			if($end && method_exists($this, $func)) {
				$name = substr($name, $end + 2);
				$fields = explode('And', $name);
				if(count($fields) == count($args)) {
					$where = array();
					for($i=0; $i<count($fields); $i++) {
						$where[ucwords($fields[$i])] = $args[$i];
					}
					$results = $this->$func(array('where'=>$where));
				}
			}
		}
		return $results;
	}

	// Try to determine a query to run
	function __call($name, $args)
	{
		if(substr($name, 0, 4) == 'find') {
			if($results = $this->dynamicFind($name, $args)) {
				return $results;
			}
		}
		return parent::__call($name, $args);
	}
}

