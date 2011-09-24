<?php

// constants
define("OBJECT", "OBJECT", true);
define("ARRAY_A", "ARRAY_A", true);
define("ARRAY_N", "ARRAY_N", true);

// autoload classes
function __autoload($class_name)
{
	$file = dirname(__FILE__).'/../../includes/class.'.$class_name.'.php';
	if(!is_file($file)) {
		$file = dirname(__FILE__).'/../../includes/system/class.'.$class_name.'.php';
	}
	if(!is_file($file)) {
		$file = dirname(__FILE__).'/../../includes/model/class.'.$class_name.'.php';
	}
	if(!is_file($file)) {
		$file = dirname(__FILE__).'/../../includes/model/base/class.'.$class_name.'.php';
	}
	require_once $file;
}

?>
