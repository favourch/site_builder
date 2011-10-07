<?php

define('DATA_MODELER_VERSION', '2.1');

class base_dm extends data_util
{
	protected $dmVersion;

	protected $dbInfo;
	protected $db;

	protected $baseUrl;
	protected $basePath;
	protected $baseWebPath;
	protected $baseTemplatePath;
	protected $baseResourcePath;
	protected $baseResourceUrl;
	protected $baseImageUrl;

	protected $siteTitle;
	protected $headerFile;
	protected $footerFile;
	protected $css;
	protected $js;

	function base_dm($connect=true)
	{
		$this->dmVersion = DATA_MODELER_VERSION;

		if($connect) {
			$this->connectDb();
		}
	}

	function connectDb($dbInfo=null)
	{
		if($dbInfo === null) {
			$this->dbInfo = new dbInfo();
		}
		else {
			$this->dbInfo = $dbInfo;
		}
		$this->db = new db($this->dbInfo->host(), $this->dbInfo->user(), $this->dbInfo->pass(), $this->dbInfo->name());
	}

	function setVars($siteTitle='Data Modeler Test Site', $url='http://localhost/', $path='/usr/local/www/data/')
	{
		$this->setPathVars($url, $path);
		$this->setFormatVars($siteTitle);
	}

	function setPathVars($url='', $path='')
	{
		$this->baseUrl			= $url;
		$this->basePath			= $path;
		$this->baseWebPath		= $path.'web/';
		$this->baseTemplatePath	= $path.'templates/';
	}

	function setFormatVars($title='')
	{
		$this->baseResourcePath	= $this->basePath.'resources/';
		$this->baseResourceUrl	= $this->baseUrl.'resources/';
		$this->baseImageUrl		= $this->baseUrl.'resources/images/';

		$this->siteTitle		= $title;
		$this->headerFile		= $this->baseResourcePath.'header.php';
		$this->footerFile		= $this->baseResourcePath.'footer.php';
		$this->css				= array();
		$this->js				= array();

		$this->addCSS('main');
	}

	function addCSS($file='')
	{
		$this->css[] = $this->baseUrl.'resources/'.$file.'.css';
	}

	function addJS($file='')
	{
		$this->js[] = $this->baseUrl.'resources/'.$file.'.js';
	}

	function redirect($url)
	{
		header('Location: '.$this->baseUrl.$url);
		exit();
	}

	function getTemplate($template, $args=array())
	{
		$templateFile = $this->baseTemplatePath.$template.'.php';
		if(!file_exists($templateFile)) {
			throw new Exception('Template not found: '.$templateFile);
			return false;
		}

		extract($args);
		if(!isset($dm)) {
			global $dm;
		}

		include($templateFile);
		return true;
	}




	function __call($name, $args)
	{
		if(isset($this->{$name})) {
			return $this->{$name};
		}
		elseif(class_exists($name)) {
			return $this->{$name} = new $name();
		}
		else {
			throw new Exception('Unknown ['.get_class($this).'] function: '.$name);
			return false;
		}
	}
}

