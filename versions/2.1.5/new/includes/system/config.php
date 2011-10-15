<?php

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
	if(!is_file($file)) {
		trigger_error("Class $class_name not found.", E_USER_ERROR);
	}
	require_once $file;
}

?>
