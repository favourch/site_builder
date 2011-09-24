<?php

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

function copyDir($last_dir='', $new_dir='', $update_dir='')
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
			copyDir($last_file, $new_file, $update_file);
		}
		else {
			$copy = false;
			if(!is_file($last_file)) {
				if(!is_dir($update_dir)) {
					makeDir($update_dir);
				}
				exec("cp $new_file $update_file");
				echo "N $new_file\r\n";
			}
			elseif(file($last_file) != file($new_file)) {
				if(!is_dir($update_dir)) {
					makeDir($update_dir);
				}
				exec("cp $new_file $update_file");
				echo "U $new_file\r\n";
			}
		}
	}
}

function makeDir($dir)
{
	$parts = explode('/', $dir);
	if(count($parts > 0)) {
		array_pop($parts);
		$new_dir = implode('/', $parts);
		if(!is_dir($new_dir)) {
			makeDir($new_dir);
		}
		echo "Making dir $dir\r\n";
		exec("mkdir $dir");
	}
}

function fillVersion($v)
{
	$tmp = explode('.', $v);
	for($i=count($tmp); $i<3; $i++) {
		$tmp[$i] = 0;
	}

	return implode('.', $tmp);
}


if($argc <= 1) {
	die("Please specify the version to create.\r\n");
}

$version = $argv[1];
$path = dirname(__FILE__).'/versions';
$last_version = getLastVersion($path);

if($version == 'last') {
	die("The last version is $last_version.\r\n");
}
elseif($version == 'major') {
	$v = fillVersion($last_version);
	$tmp = explode('.', $v);
	$tmp[0]++;
	$version = "$tmp[0].0";
}
elseif($version == 'minor') {
	$v = fillVersion($last_version);
	$tmp = explode('.', $v);
	$tmp[1]++;
	$version = "$tmp[0].$tmp[1]";
}
elseif($version == 'bug') {
	$v = fillVersion($last_version);
	$tmp = explode('.', $v);
	$tmp[2]++;
	$version = "$tmp[0].$tmp[1].$tmp[2]";
}

$new_dir = "$path/$version";
if(is_dir($new_dir)) {
	die("Version $version already exists.\r\n");
}

echo "Creating version $version.\r\n";
mkdir($new_dir);
mkdir("$new_dir/new");
mkdir("$new_dir/update");

echo "Copying all files to \"new\" directory.\r\n";
exec("cp -R ./current/* $new_dir/new/");

if($last_version) {
	echo "Copying all updated/new files to \"update\" directory. Review the list below to ensure accuracy. (N=new, U=updated)\r\n";
	copyDir("$path/$last_version/new", dirname(__FILE__).'/current', "$new_dir/update");
}
else {
	echo "This is the first version. Copying all files to \"update\" directory.\r\n";
	exec("cp -R ./current/* $new_dir/update/");
}

?>
