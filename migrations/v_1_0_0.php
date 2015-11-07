<?php
/**
*
* @package Avatar Upload
* @copyright BB3.Mobi 2015 (c) Anvar (apwa.ru)
*/

namespace bb3mobi\AvatarUpload\migrations;

class v_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['avatar_upload_version']) && version_compare($this->config['avatar_upload_version'], '1.0.0', '>=');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}
	public function update_schema()
	{
		return array();
	}

	public function revert_schema()
	{
		return array();
	}

	public function update_data()
	{
		return array(
			// Add configs
			//array('config.add', array('avatar_upload_filesize', '512000')),
			array('config.add', array('avatar_upload_max_width', '1200')),
			array('config.add', array('avatar_upload_max_height', '1200')),

			// Current version
			array('config.add', array('avatar_upload_version', '1.0.0')),
		);
	}

	public function revert_data()
	{
		return array(
			// Remove configs
			//array('config.remove', array('avatar_upload_filesize')),
			array('config.remove', array('avatar_upload_max_width')),
			array('config.remove', array('avatar_upload_max_height')),

			// Remove version
			array('config.remove', array('avatar_upload_version')),
		);
	}
}
