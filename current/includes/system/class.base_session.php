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

		$this->sessionId	= session_id();
		$this->sessionData	= $_SESSION;
		$this->userId		= isset($this->sessionData['user']['userId']) ? $this->sessionData['user']['userId'] : 0;
		$this->adminId		= isset($this->sessionData['admin']['adminId']) ? $this->sessionData['admin']['adminId'] : 0;
		unset($_SESSION['message']);
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
		$this->sessionData['user']['userId']		= $user->userId;
		$_SESSION['user']['userId']					= $user->userId;
		$this->sessionData['user']['username']		= $user->username;
		$_SESSION['user']['username']				= $user->username;
		$this->sessionData['user']['first_name']	= $user->firstName;
		$_SESSION['user']['first_name']				= $user->firstName;
	}

	function adminLoggedIn()
	{
		if($this->adminId) {
			return true;
		}
		return false;
	}

	function adminLogin($admin)
	{
		$this->adminId = $admin->adminId;
		$this->sessionData['admin']['adminId']		= $admin->adminId;
		$_SESSION['admin']['adminId']				= $admin->adminId;
		$this->sessionData['admin']['username']		= $admin->username;
		$_SESSION['admin']['username']				= $admin->username;
	}

	function logout()
	{
		unset($_SESSION);
		$this->destroy();
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
	/** end DATA **/
}

