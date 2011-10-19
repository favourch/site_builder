<?php

// Sorts versions numerically. Regular PHP sort function is unable to sort with type x.x.x numerically.
function sortVersions($v1, $v2)
{
	$tmp1 = array_merge(explode('.', $v1), array(0, 0, 0));
	$tmp2 = array_merge(explode('.', $v2), array(0, 0, 0));

	for($i=0; $i<3; $i++) {
		if(!$tmp1[$i]) { $tmp1[$i] = 0; }
		if(!$tmp2[$i]) { $tmp2[$i] = 0; }

		if($tmp1[$i] > $tmp2[$i]) {
			return 1;
		}
		elseif($tmp1[$i] < $tmp2[$i]) {
			return -1;
		}
	}

	return 1;
}

// Get the last version created.
function getLastVersion($path='')
{
	$version_list = array();
	$dh = opendir($path);
	while($dir = readdir($dh)) {
		if(is_dir("$path/$dir") && !in_array($dir, array('.', '..'))) {
			$version_list[] = $dir;
		}
	}
	usort($version_list, 'sortVersions');

	return end($version_list);
}

// Check each file in a directory and copy that file to "update" directory if it's new. If a directory
// is found, recursively call this function for each directory.
function copyDir($last_dir='', $new_dir='', $update_dir='', &$file_list)
{
	$dh = opendir($new_dir);
	while($file = readdir($dh)) {
		if(in_array($file, array('.', '..')) || substr($file, -4) == '.swp') {
			continue;
		}
		$last_file = "$last_dir/$file";
		$new_file = "$new_dir/$file";
		$update_file = "$update_dir/$file";
		if(is_dir($new_file)) {
			copyDir($last_file, $new_file, $update_file, $file_list);
		}
		else {
			$copy = false;
			if(!is_file($last_file)) {
				if(!is_dir($update_dir)) {
					makeDir($update_dir);
				}
				$file_list[] = 'N '.str_replace(dirname(__FILE__).'/current/', '', $new_file);
				echo "N $new_file\r\n";
				$new_file = str_replace(' ', '\ ', $new_file);
				$update_file = str_replace(' ', '\ ', $update_file);
				exec("cp $new_file $update_file");
			}
			elseif(file($last_file) != file($new_file)) {
				if(!is_dir($update_dir)) {
					makeDir($update_dir);
				}
				$file_list[] = 'U '.str_replace(dirname(__FILE__).'/current/', '', $new_file);
				echo "U $new_file\r\n";
				$new_file = str_replace(' ', '\ ', $new_file);
				$update_file = str_replace(' ', '\ ', $update_file);
				exec("cp $new_file $update_file");
			}
		}
	}
}

// Make a new directory in "update". If the parent directory does not exist, call this function recursively
// to create all necessary parent directories.
function makeDir($dir)
{
	$parts = explode('/', $dir);
	if(count($parts > 0)) {
		array_pop($parts);
		$new_dir = implode('/', $parts);
		if(!is_dir($new_dir)) {
			makeDir($new_dir);
		}
		exec("mkdir $dir");
	}
}

// Convert any minor/major versions (x.x) to full version number for incrementation (x.x.x).
function fillVersion($v)
{
	$tmp = explode('.', $v);
	for($i=count($tmp); $i<3; $i++) {
		$tmp[$i] = 0;
	}

	return implode('.', $tmp);
}

// Update the version number shown in "base_dm" class.
function updateVersionNumber($version)
{
	$file = dirname(__FILE__).'/current/includes/system/class.base_dm.php';
	if(!is_file($file)) {
		die("Unable to find base_dm class at $file");
	}

	$fh = fopen($file, 'r');
	$text = fread($fh, filesize($file));
	fclose($fh);

	$start = strpos($text, 'DATA_MODELER_VERSION') + 22;
	$start = strpos($text, "'", $start) + 1;
	$end = strpos($text, "'", $start);

	$newText = substr($text, 0, $start) . $version . substr($text, $end);

	$fh = fopen($file, 'w');
	fwrite($fh, $newText);
	fclose($fh);
}


// Must specify either a specific version, "last" (display last version), "major" (new major version),
// "minor" (new minor version), or "bug" (new bug fix version).
if($argc <= 1) {
	die("Please specify the version to create.\r\n");
}

// Get the new and last version.
$version = $argv[1];
$path = dirname(__FILE__).'/versions';
$last_version = getLastVersion($path);

// Display the last version, then quit.
if($version == 'last') {
	die("The last version is $last_version.\r\n");
}
// Get the version number for a new major version.
elseif($version == 'major') {
	$v = fillVersion($last_version);
	$tmp = explode('.', $v);
	$tmp[0]++;
	$version = "$tmp[0].0";
}
// Get the version number for a new minor version.
elseif($version == 'minor') {
	$v = fillVersion($last_version);
	$tmp = explode('.', $v);
	$tmp[1]++;
	$version = "$tmp[0].$tmp[1]";
}
// Get the version number for a new bug version.
elseif($version == 'bug') {
	$v = fillVersion($last_version);
	$tmp = explode('.', $v);
	$tmp[2]++;
	$version = "$tmp[0].$tmp[1].$tmp[2]";
}

// Make sure this version doesn't already exist.
$new_dir = "$path/$version";
if(is_dir($new_dir)) {
	die("Version $version already exists.\r\n");
}

// Update "base_dm" class to show new version number.
updateVersionNumber($version);

// Create the base directories for the version.
echo "Creating version $version.\r\n";
mkdir($new_dir);
mkdir("$new_dir/new");
mkdir("$new_dir/update");

// Copy all files to "new" directory for any new installations.
echo "Copying all files to \"new\" directory.\r\n";
exec("cp -R ./current/* $new_dir/new/");

// If there is a version prior to this, find and copy all updated files to the "update" directory.
if($last_version) {
	$file_list = array();
	echo "Copying all updated/new files to \"update\" directory. Review the list below to ensure accuracy. (N=new, U=updated)\r\n";
	copyDir("$path/$last_version/new", dirname(__FILE__).'/current', "$new_dir/update", $file_list);

	$files_string = implode("\n", $file_list);
	$update_text = <<<EOT
This is a list of files that were new or updated in version $version.
N = New file
U = Updated file

$files_string

EOT;
	$update_text = str_replace("\r\n", "\n", $update_text);

	$fh = fopen("$new_dir/files.txt", 'w');
	fwrite($fh, $update_text);
	fclose($fh);
}
// If this is the first version, copy all files to the "update" directory.
else {
	echo "This is the first version. Copying all files to \"update\" directory.\r\n";
	exec("cp -R ./current/* $new_dir/update/");
}

?>
