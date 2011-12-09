<?php

class base_dbInfo extends util
{
	protected $host;
	protected $user;
	protected $pass;
	protected $name;
	protected $debug;

	function base_dbInfo()
	{
		$data = $this->load_config('db');

		if ($data !== false) {
			$this->setHost($data['host']);
			$this->setUser($data['username']);
			$this->setPass($data['password']);
			$this->setName($data['database']);
			$this->setDebug($data['debug']);
		}
	}

	function setHost($host='')
	{
		$this->host = $host;
	}

	function setUser($user='')
	{
		$this->user = $user;
	}

	function setPass($pass='')
	{
		$this->pass = $pass;
	}

	function setName($name='')
	{
		$this->name = $name;
	}

	function setDebug($debug=null)
	{
		$this->debug = $debug;
	}
}

