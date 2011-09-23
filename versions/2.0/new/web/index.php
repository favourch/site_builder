<?php

// Include the config file
include('../config.php');

// Show the header
include($dm->headerFile());

// Get a basic template
$dm->getTemplate('index', array('version'=>$dm->dmVersion()));

// Show the footer
include($dm->footerFile());

