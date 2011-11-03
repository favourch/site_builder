<?php

// Include the system config to define some things
require_once dirname(__FILE__).'/includes/system/config.php';

// Create the global Data Modeler object
$dm = new dm(true);

// Set a couple of site variables (Site Name, Base Url, Base File Path)
$site = util::load_config('site');
$dm->setVars($site['title'], $site['base_url'], $site['base_path']);

?>
