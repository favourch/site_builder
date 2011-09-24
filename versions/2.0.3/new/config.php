<?php

// Include the system config to define some things
require_once dirname(__FILE__).'/includes/system/config.php';

// Create the global Data Modeler object
$dm = new dm(true);

// Set a couple of site variables (Site Name, Base Url, Base File Path)
$dm->setVars('Site Builder', 'http://site_builder.stoddarthome.com/', '/usr/local/www/data/site_builder/current/');

?>
