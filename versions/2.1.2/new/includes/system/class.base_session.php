<?php

class base_session extends util
{
	protected $sessionId;
	protected $userId;
	protected $adminId;
	protected $sessionData;

	function base_session()
	{
		session_start();

		unset($_SESSION['message']);
		$this->sessionId	= session_id();
		$this->sessionData	= $_SESSION;
		$this->userId		= isset($this->sessionData['user:userId']) ? $this->sessionData['user:userId'] : 0;
		$this->adminId		= isset($this->sessionData['admin:userId']) ? $this->sessionData['admin:userId'] : 0;
	}


	/** begin LOGIN/LOGOUT **/
	function loggedIn()
	{
		if($this->userId) {
			return true;
		}
		return false;
	}

	function login($user)
	{
		$this->userId	= $user->userId;
		$this->setData('user:userId', $user->userId);
		$this->setData('user:username', $user->username);
	}

	function adminLoggedIn()
	{
		if($this->adminId) {
			return true;
		}
		return false;
	}

	function adminLogin($user)
	{
		$this->adminId = $user->userId;
		$this->setData('admin:userId', $user->userId);
		$this->setData('admin:username', $user->username);
	}

	function logout($type='')
	{
		switch($type) {
			case 'user':
				$this->userId = null;
				$this->unsetData('user:userId');
				$this->unsetData('user:username');
				break;

			case 'admin':
				$this->adminId = null;
				$this->unsetData('admin:userId');
				$this->unsetData('admin:username');
				break;

			default:
				$this->userId = null;
				$this->adminId = null;
				$this->unsetData('user:userId');
				$this->unsetData('user:username');
				$this->unsetData('admin:userId');
				$this->unsetData('admin:username');
				break;
		}
	}

	function destroy()
	{
		session_destroy();
	}
	/** end LOGIN/LOGOUT **/


	/** begin MESSAGES **/
	function setMessage($name, $message)
	{
		if($name && $message) {
			$this->sessionData['message'][$name]	= $message;
			$_SESSION['message'][$name]				= $message;
		}
	}

	function hasMessage($name)
	{
		if($name) {
			return isset($this->sessionData['message'][$name]) ? true : false;
		}
		return false;
	}

	function getMessage($name)
	{
		if($name) {
			return $this->sessionData['message'][$name];
		}
		return false;
	}
	/** end MESSAGES **/


	/** begin DATA **/
	function setData($name, $value)
	{
		$this->sessionData[$name]	= $value;
		$_SESSION[$name]			= $value;
	}

	function hasData($name)
	{
		if($name) {
			return isset($this->sessionData[$name]) ? true : false;
		}
		return false;
	}

	function getData($name)
	{
		if($this->hasData($name)) {
			return $this->sessionData[$name];
		}
		return false;
	}

	function unsetData($name)
	{
		if($this->hasData($name)) {
			$this->sessionData[$name]	= null;
			$_SESSION[$name]			= null;

			unset($this->sessionData[$name]);
			unset($_SESSION[$name]);
		}
	}
	/** end DATA **/
}

