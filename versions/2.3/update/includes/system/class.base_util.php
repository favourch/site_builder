<?php

class base_util
{
	function base_util()
	{
	}

	public static function load_config($name)
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
			list($key, $val) = explode(':', $row, 2);
			$key = trim($key);
			$val = trim($val);

			$data[$key] = $val;
		}

		fclose($fp);

		return $data;
	}

	public static function isCli()
	{
		return (bool)defined('STDIN');
	}

	public static function cliDisplay($str, $append="\n")
	{
		if(self::isCli())
		{
			echo "$str$append";
		}
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

