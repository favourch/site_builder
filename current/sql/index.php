<?php

include_once('../config.php');

$__pageTitle = 'DB Manager';

include($dm->headerFile());

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

if(isset($_POST['file_list'])) {
	foreach($_POST['file_list'] as $file) {
		echo "Processing $dir$file ... ";
		$sql = explode(';', file_get_contents($dir.$file));
		foreach($sql as $s) {
			if($query = trim($s)) {
				$query = str_replace('<<<SCRIPT_PATH>>>', $dm->basePath().'sql/scripts/', $query);
				$dm->db()->query($query);
			}
		}
		echo "Done.<br />\r\n";
	}
}

if(isset($_POST['build_model'])) {
	$skip_config = true;
	include('../scripts/build_model.php');
}

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

