<?php

include_once('../config.php');

function className($table)
{
	$str = str_replace('_', ' ', $table);
	$str = ucwords($str);
	$str = str_replace(' ', '', $str);
	return $str;
}

echo 'Building models...<br />
<div style="padding: 0px 0px 0px 15px">
';

$sql = "show tables";
foreach($dm->db()->getResults($sql) as $row)
{
	$table	= $row->{'Tables_in_'.$dm->dbInfo()->name()};
	$sql	= "describe `$table`";
	$fields	= $dm->db()->getResults($sql);
	$edit	= array();

	/**** BASE MODEL CLASS FILE ****/
	$fieldDecl = '';
	foreach($fields as $field) {
		$fieldDecl .= "	protected \$$field->Field; // Type: $field->Type, Null: $field->Null, Key: $field->Key\r\n";
		$edit[]		= $field;
	}
	array_shift($edit);

	$fieldArr = "\r\n";
	foreach($edit as $field) {
		$fieldArr .= "\t\t\t\t'$field->Field'=>'$field->Field',\r\n";
	}
	$fieldArr .= "\t\t\t";

	$filetext = "<?php

class Base".className($table)." extends data_util
{
$fieldDecl

	function Base".className($table)."(\$object=null)
	{
		\$this->__tableName	= '$table';
		\$this->__idField	= '".$fields[0]->Field."';
		\$this->__fields	= array($fieldArr);
	}
}

";

	$filename = $dm->basePath().'includes/model/base/class.Base'.className($table).'.php';

	$fp = fopen($filename, 'w');
	fwrite($fp, $filetext);
	fclose($fp);

	echo "$filename created.<br />\r\n";
	/**** END BASE MODEL CLASS FILE ****/




	/**** EXTENDED MODEL CLASS FILE ****/
	$filename = $dm->basePath().'includes/model/class.'.className($table).'.php';

	if(!file_exists($filename)) {
		$filetext = "<?php

class ".className($table)." extends Base".className($table)."
{
	function ".className($table)."(\$object=null)
	{
		parent::__construct(\$object);
	}
}

";

		$fp = fopen($filename, 'w');
		fwrite($fp, $filetext);
		fclose($fp);

		echo "$filename created.<br />\r\n";
	}
	else {
		echo "$filename exists.<br />\r\n";
	}
	/**** END EXTENDED MODEL CLASS FILE ****/
}

echo "</div>\r\nDone.<br />\r\n";

?>
