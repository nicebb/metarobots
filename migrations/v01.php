<?php

namespace nicebb\metarobots\migrations;

class v01 extends \phpbb\db\migration\migration
{

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v311');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('metarobots_indexing_enable', 1, true)),
			array('config.add', array('metarobots_following_enable', 1, true)),
		);
	}

	public function update_schema()
	{
		return array(
			'add_columns' => array(
				$this->table_prefix . 'forums' => array(
					'metarobots_indexing_enable' => array('TINT:2', 0),
					'metarobots_following_enable' => array('TINT:2', 0),
				),
			),
		);
	}
	
	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'forums' => array(
					'metarobots_indexing_enable',
					'metarobots_following_enable',
				),
			),
		);
	}
}
