<?php

class BaseSiteUser extends data_util
{
	protected $siteUserId; // Type: int(11), Null: NO, Key: PRI
	protected $username; // Type: varchar(50), Null: NO, Key: 
	protected $password; // Type: varchar(50), Null: NO, Key: 


	function BaseSiteUser($object=null)
	{
		$this->__tableName	= 'site_user';
		$this->__idField	= 'siteUserId';
		$this->__fields	= array(
				'username'=>'username',
				'password'=>'password',
			);
	}
}

