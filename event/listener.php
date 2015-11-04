<?php
/**
*
* @package Avatar Upload
* @version $Id: listener.php 2015-11-03 15:03:17 $
* @copyright BB3.Mobi 2015 (c) Anvar(http://apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3mobi\AvatarUpload\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	public function __construct(\phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\db\driver\driver_interface $db, $resize)
	{
		$this->user = $user;
		$this->request = $request;
		$this->db = $db;
		$this->resize = $resize;
	}

	static public function getSubscribedEvents()
	{
		return array(
			/* @event core.avatar_driver_upload_move_file_before
			* @var	string	destination			Destination directory where the file is going to be moved
			* @var	string	prefix				Prefix for the avatar filename
			* @var	array	row					Array with avatar row data
			* @var	array	error				Array of errors, if filled in by this event file will not be moved
			*/
			'core.avatar_driver_upload_move_file_before'	=> 'avatar_upload_move_file',
		);
	}

	public function avatar_upload_move_file($event)
	{
		if (!sizeof($event['error']))
		{
			$upload_file = $this->request->file('avatar_upload_file');
			if (!empty($upload_file['name']))
			{
				if ($result = $this->resize->avatar_upload_resize($event['row']))
				{
					// Success! Lets save the result in the database
					$result = array(
						'user_avatar_type'		=> AVATAR_UPLOAD,
						'user_avatar'			=> $result['avatar'],
						'user_avatar_width'		=> $result['avatar_width'],
						'user_avatar_height'	=> $result['avatar_height'],
					);

					$sql = 'UPDATE ' . USERS_TABLE . '
						SET ' . $this->db->sql_build_array('UPDATE', $result) . '
						WHERE user_id = ' . (int) $this->user->data['user_id'];

					$this->db->sql_query($sql);

					meta_refresh(3, build_url());
					$message = $this->user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($this->user->lang['RETURN_PAGE'], '<a href="' . build_url() . '">', '</a>');
					trigger_error($message);
				}
			}
		}
	}
}
