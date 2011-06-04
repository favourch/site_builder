<?php

class base_util
{
	function base_util()
	{
	}

	function load_config($name)
	{
		$file = "../config/$name.dmc";
		if (!file_exists($file)) {
			return false;
		}

		$fp = fopen($file, 'r');
		if ($fp === false) {
			return false;
		}

		$data = array();
		while ($row = fgets($fp)) {
			list($key, $val) = explode(':', $row);
			$key = trim($key);
			$val = trim($val);

			$data[$key] = $val;
		}

		return $data;
	}

	function __call($name, $args)
	{
		if (isset($this->{$name})) {
			return $this->{$name};
		}
		else {
			echo '<pre>';
			throw new Exception('Unknown ['.get_class($this).'] function: '.$name);
			echo '</pre>';
			return false;
		}
	}
}

