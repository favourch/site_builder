<?php

class BaseSiteUserInfo extends data_util
{
	protected $siteUserInfoId; // Type: int(11), Null: NO, Key: PRI
	protected $siteUserId; // Type: int(11), Null: NO, Key: 
	protected $info; // Type: varchar(50), Null: NO, Key: 


	function BaseSiteUserInfo($object=null)
	{
		$this->__tableName	= 'site_user_info';
		$this->__idField	= 'siteUserInfoId';
		$this->__fields	= array(
				'siteUserId'=>'siteUserId',
				'info'=>'info',
			);
	}
}

