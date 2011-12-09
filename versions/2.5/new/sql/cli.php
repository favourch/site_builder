<?php

chdir(dirname(__FILE__));

if($argc > 0 && $argv[0] == $_SERVER['PHP_SELF']) {
	$argc--;
	array_shift($argv);
}

if($argc == 0) {
	echo <<<EOT

Use the "help" command (e.g. cli_db help) to learn how to use this script.


EOT;
	die();
}

if($argv[0] == 'help') {
	echo <<<EOT

----- Begin cli_db Help -----

This script is used to run SQL files via the command line. The examples shown are
for when running the cli_db executable. You may also call this file via PHP, such
as "php cli.php". All SQL files should be placed in the sql/scripts/ directory of
your project, and follow the naming scheme ## - file.sql. For example, you might
have the following files:

01 - init.sql
02 - insertData.sql
03 - modifyUserTable.sql
04 - addTemplatesTable.sql

The text is just a description to help you identify the files and should be any
helpful title.

To see the list of files, use the "list" command (e.g. cli_db list).

To run a specific file, use the "run" command (e.g. cli_db run 1). You may
also run multiple files by listing more than one (e.g. cli_db run 1 2 4). To
run a range of files, list the beginning and ending files separated with a dash
(e.g. php.cliphp run 1-4).

To build models, use the "bm" option of the "run" command (e.g. cli_db run bm).
This can be combined with running other scripts, but the "bm" option should always
be at the end to ensure all scripts have run (e.g. cli_db run 1-3 bm).

To run all scripts, use the "all" option of the "run" command, which can also be
combined with the "bm" option (e.g. cli_db run all bm).

----- End cli_db Help -----


EOT;
	die();
}

if($argv[0] == 'list') {
	$dir = './scripts/';
	$files = array();
	$dh = opendir($dir);
	while($file = readdir($dh)) {
		if(substr($file, -4) == '.sql') {
			$files[] = $file;
		}
	}
	closedir($dh);
	sort($files);

	foreach($files as $f) {
		echo "$f\n";
	}
	die();
}

if($argv[0] == 'run') {
	array_shift($argv);
	$dir = './scripts/';
	$files = array();
	$dh = opendir($dir);
	while($file = readdir($dh)) {
		if(substr($file, -4) == '.sql') {
			list($num, $junk) = explode('-', $file, 2);
			$files[intval($num)] = $file;
		}
	}
	closedir($dh);
	asort($files);
	$fileCount = 0;
	foreach($argv as $arg) {
		$run = false;
		$buildModel = false;
		$fileList = array();
		if(strpos($arg, '-')) {
			list($lower, $upper) = explode('-', $arg);
			$lower = intval($lower);
			$upper = intval($upper);
			for($i=$lower; $i<=$upper; $i++) {
				if(array_key_exists($i, $files)) {
					$fileCount++;
					$run = true;
					$fileList[] = $files[$i];
				}
			}
		}
		elseif(is_numeric($arg) && array_key_exists(intval($arg), $files)) {
			$fileCount++;
			$run = true;
			$fileList[] = $files[intval($arg)];
		}
		elseif($arg == 'all') {
			foreach($files as $f) {
				$fileCount++;
				$run = true;
				$fileList[] = $f;
			}
		}
		elseif($arg == 'bm') {
			$run = true;
			$buildModel = true;
		}
		if($run) {
			include('index.php');
		}
	}
	echo "Ran $fileCount files.\n";
}

echo <<<EOT

Use the "help" command (e.g. cli_db help) to learn how to use this script.


EOT;

