<?php

define('DATA_MODELER_VERSION', '2.0');

class base_dm extends util
{
	protected $dmVersion;

	protected $dbInfo;
	protected $db;

	protected $baseUrl;
	protected $basePath;
	protected $baseWebPath;

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
			$this->dbInfo	= new dbInfo();
		}
		else {
			$this->dbInfo	= $dbInfo;
		}
		$this->db	= new db($this->dbInfo->host(), $this->dbInfo->user(), $this->dbInfo->pass(), $this->dbInfo->name());
	}

	function setVars($siteTitle='Test Site', $url='http://local.web/dm/', $path='c:/Program Files/Web/dm/')
	{
		$this->setPathVars($url, $path);
		$this->setFormatVars($siteTitle);
	}

	function setPathVars($url='', $path='')
	{
		$this->baseUrl		= $url;
		$this->basePath		= $path;
		$this->baseWebPath	= $path.'web/';
	}

	function setFormatVars($title='')
	{
		$this->siteTitle	= $title;
		$this->headerFile	= $this->basePath.'resources/header.php';
		$this->footerFile	= $this->basePath.'resources/footer.php';
		$this->css			= array();
		$this->js			= array();

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




	function __call($name, $args)
	{
		if(isset($this->{$name})) {
			return $this->{$name};
		}
		elseif(class_exists($name)) {
			return $this->{$name} = new $name();
		}
		else {
			echo '<pre>';
			throw new Exception('Unknown ['.get_class($this).'] function: '.$name);
			echo '</pre>';
			return false;
		}
	}
}

