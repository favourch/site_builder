<?php

/*
 * This script runs some tests on the base classes
 */
include('../config.php');
$tests = array();


// Test that the "dm" object got loaded correctly
$tests[] = array('test'=>'Current Version', 'results'=>$dm->dmVersion());


// Test the database scripts
chdir($dm->basePath());
$results = array();
exec($dm->basePath().'cli_db list', $results);
$results = implode("<br />\n", $results);
$tests[] = array('test'=>'cli_db list', 'results'=>$results);

chdir($dm->basePath());
$results = array();
exec($dm->basePath().'cli_db run 1-2 bm', $results);
$results = implode("<br />\n", $results);
$tests[] = array('test'=>'cli_db run 1-2 bm', 'results'=>$results);


// Test the config script
chdir(dirname(__FILE__));
$data = util::load_config('site');
$tests[] = array('test'=>'Site Config File', 'results'=>"Title: $data[title], Base URL: $data[base_url], Base Path: $data[base_path]");


// Find a specific row in the database
$row = $dm->SiteBuilder()->find(1);
$tests[] = array('test'=>'Retrieve record 1 from site_builder table', 'results'=>print_r($row, true));


// Add a row to the database
$row = array('name'=>'Another test. ' . microtime());
$id = $dm->SiteBuilder()->add($row);
$row = $dm->SiteBuilder()->find($id);
$tests[] = array('test'=>"Add/retrieve record $id to site_builder table", 'results'=>print_r($row, true));


// Update a row in the database
$row = array('id'=>$id, 'name'=>'Some other test. ' . microtime());
$dm->SiteBuilder()->update($row);
$row = $dm->SiteBuilder()->find($id);
$tests[] = array('test'=>"Update record $id in site_builder table", 'results'=>print_r($row, true));


// Delete a row from the database
$dm->SiteBuilder()->delete($id);
$row = $dm->SiteBuilder()->find($id);
ob_start();
var_dump($row);
$result = ob_get_clean();
$tests[] = array('test'=>"Delete record $id from site_builder table", 'results'=>$result);


// Add and lookup one row using the dynamic find
$row = array('name'=>'Lookup row', 'extra'=>'Extra data');
$id = $dm->SiteBuilder()->add($row);
$row = $dm->SiteBuilder()->findOneByNameAndExtra('Lookup row', 'Extra data');
$tests[] = array('test'=>'Use dynamic find for one row', 'results'=>print_r($row, true));


// Add and lookup multiple rows using the dynamic find
$results = $dm->SiteBuilder()->findByExtra('Extra data');
$tests[] = array('test'=>'Use dynamic find for multiple rows', 'results'=>'<pre>'.print_r($results, true).'</pre>');


// Login/logout test user
$user = (object)null;
$user->userId = 123;
$user->username = 'TestUser';
$dm->session()->login($user);
$tests[] = array('test'=>'Log in tests.', 'results'=>'Logged in - '.intval($dm->session()->loggedIn()).'. As '.$dm->session()->getData('user:username'));
$dm->session()->logout('user');
$tests[] = array('test'=>'Log out test.', 'results'=>'Logged in - '.intval($dm->session()->loggedIn()));


// Total query information
$queries = $dm->db()->getQueryTotals();
$tests[] = array('test'=>'Total queries and time', 'results'=>"$queries[count] queries took ".number_format($queries[time], 5)." seconds.");


// Output the page
include($dm->headerFile());
$dm->getTemplate('test', array('tests'=>$tests));
include($dm->footerFile());

