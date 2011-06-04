<?php

if($argc <= 1) {
	die("Please specify the version to create.\r\n");
}

$version = $argv[1];
$new_dir = dirname(__FILE__).'/versions/'.$version;
if(is_dir($new_dir)) {
	die("Version $version already exists\r\n");
}

echo "Creating version directory\r\n";
mkdir($new_dir);
mkdir("$new_dir/new");
mkdir("$new_dir/update");

echo "Copying all files to \"new\" directory\r\n";
exec("cp -R ./current/* $new_dir/new/");

echo "NOTE: Remember to add changed files to \"$new_dir/update/\" directory\r\n";

?>
