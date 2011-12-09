<?php

include_once('../config.php');

if(!isset($fileList)) {
	$fileList = isset($_POST['file_list']) ? $_POST['file_list'] : array();
}
if(!isset($buildModel)) {
	$buildModel = isset($_POST['model_model']) ? $_POST['build_model'] : false;
}

$html = '';
foreach($fileList as $file) {
	$html .= "Processing $dir$file ... ";
	util::cliDisplay("Processing $dir$file ...");
	$sql = explode(';', file_get_contents($dir.$file));
	foreach($sql as $s) {
		if($query = trim($s)) {
			$query = str_replace('<<<SCRIPT_PATH>>>', $dm->basePath().'sql/scripts/', $query);
			util::cliDisplay("----\n$query");
			$dm->db()->query($query);
		}
	}
	util::cliDisplay("----\nFinished.", "\n\n");
	$html .= "Done.<br />\n";
}

if($buildModel) {
	$skip_config = true;
	include('../includes/system/build_model.php');
}

if(!util::isCli()) {
	$__pageTitle = 'DB Manager';

	$dir 	= './scripts/';
	$files	= array();
	$dh		= opendir($dir);
	while($file = readdir($dh)) {
		if(substr($file, -4) == '.sql') {
			$files[] = $file;
		}
	}
	closedir($dh);
	sort($files);

	include($dm->headerFile());
	echo $html;
?>
<form method="post">
<select name="file_list[]" multiple>
<?php foreach($files as $f) : ?>
	<option value="<?php echo $f; ?>"><?php echo $f; ?></option>
<?php endforeach; ?>
</select>
<br />
<input type="checkbox" name="build_model"> Build Models
<br />
<input type="submit" value="Process">
</form>
<?php
	include($dm->footerFile());
}

