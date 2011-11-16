<?php

class BaseSiteBuilder extends data_util
{
	protected $id; // Type: int(11), Null: NO, Key: PRI
	protected $name; // Type: varchar(50), Null: NO, Key: 
	protected $extra; // Type: varchar(50), Null: NO, Key: 


	function BaseSiteBuilder($object=null)
	{
		$this->__tableName	= 'site_builder';
		$this->__idField	= 'id';
		$this->__fields	= array(
				'name'=>'name',
				'extra'=>'extra',
			);
	}
}

