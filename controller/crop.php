<?php
/**
*
* @package Avatar Upload Crop
* @copyright BB3.Mobi 2015 (c) Anvar(http://apwa.ru)
* @version $Id: crop.php 2015-11-06 18:58:10 $
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace bb3mobi\AvatarUpload\controller;

class crop
{
	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var string phpbb_root_path */
	protected $phpbb_root_path;

	/** @var string phpEx */
	protected $php_ext;

	public function __construct(\phpbb\template\template $template, \phpbb\config\config $config, \phpbb\user $user, \phpbb\request\request_interface $request, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, $phpbb_root_path, $php_ext)
	{
		$this->template = $template;
		$this->config = $config;
		$this->user = $user;
		$this->request = $request;
		$this->db = $db;
		$this->helper = $helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function avatar_crop($avatar_id)
	{
		$extension = $this->request->variable('ext', '');
		$submit = $this->request->is_set_post('submit');

		$prefix = $this->config['avatar_salt'] . '_';

		// Calculate new destination
		$destination = $this->config['avatar_path'];

		// Adjust destination path (no trailing slash)
		if (substr($destination, -1, 1) == '/' || substr($destination, -1, 1) == '\\')
		{
			$destination = substr($destination, 0, -1);
		}

		$destination = str_replace(array('../', '..\\', './', '.\\'), '', $destination);
		if ($destination && ($destination[0] == '/' || $destination[0] == "\\"))
		{
			$destination = '';
		}

		$destination_file = $this->phpbb_root_path . $destination . '/' . $prefix . $avatar_id . '.' . $extension;

		$destination_old = 'ext/bb3mobi/AvatarUpload/images';
		$destination_old_file = $this->phpbb_root_path . $destination_old . '/' . $avatar_id . '.' . $extension;

		$this->user->setup('ucp');
		$this->user->add_lang_ext('bb3mobi/AvatarUpload', 'avatar_upload');

		$error = array();

		if ($this->user->data['user_id'] != $avatar_id)
		{
			trigger_error('NO_AVATAR_USER');
		}

		if (!$extension || !file_exists($destination_old_file))
		{
			trigger_error('NO_AVATAR_FILES');
		}

		if (($image_info = @getimagesize($destination_old_file)) == false)
		{
			trigger_error('NO_AVATAR_FILES');
		}

		$avatar_width = $image_info[0];
		$avatar_height = $image_info[1];

		$params_size = array(
			'x1'	=> $this->request->variable('x1', 0),
			'y1'	=> $this->request->variable('y1', 0),
			'x2'	=> ceil($this->request->variable('x2', $image_info[0])),
			'y2'	=> ceil($this->request->variable('y2', $image_info[1])),
			'w'		=> floor($this->request->variable('w', $image_info[0])),
			'h'		=> floor($this->request->variable('h', $image_info[1])),
			'ext'	=> (string) $extension,
		);

		if ($submit)
		{
			if ($params_size['w'] < $this->config['avatar_min_width'] || $params_size['x1'] > $avatar_width-$this->config['avatar_max_width'])
			{
				$error[] = $this->user->lang['ERROR_AVATAR_W'];
			}

			if ($params_size['h'] < $this->config['avatar_min_height'] || $params_size['y1'] > $avatar_height-$this->config['avatar_max_height'])
			{
				$error[] = $this->user->lang['ERROR_AVATAR_H'];
			}

			if ($params_size['x2'] > $avatar_width || $params_size['x2'] < $this->config['avatar_min_width'])
			{
				$error[] = $this->user->lang['ERROR_AVATAR_X2'];
			}

			if ($params_size['y2'] > $avatar_height || $params_size['y2'] < $this->config['avatar_min_height'])
			{
				$error[] = $this->user->lang['ERROR_AVATAR_Y2'];
			}
		}

		if (!sizeof($error) && $submit)
		{
			if ($result = $this->resize($params_size, $destination_old, $destination_old_file))
			{
				rename($destination_old_file, $destination_file);

				// Success! Lets save the result in the database
				$result = array(
					'user_avatar_type'		=> AVATAR_UPLOAD,
					'user_avatar'			=> $avatar_id . '_' . time() . '.' . $extension,
					'user_avatar_width'		=> $result['avatar_width'],
					'user_avatar_height'	=> $result['avatar_height'],
				);

				$sql = 'UPDATE ' . USERS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', $result) . '
					WHERE user_id = ' . (int) $this->user->data['user_id'];

				$this->db->sql_query($sql);

				meta_refresh(3, generate_board_url(), true);
				$message = $this->user->lang['PROFILE_UPDATED'] . '<br /><br />' . sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . generate_board_url() . '">', '</a>');
				trigger_error($message);
			}
		}

		$this->template->assign_vars(array(
			'ERROR'				=> (sizeof($error)) ? implode('<br />', $error) : '',
			'AVATAR_FILE'		=> generate_board_url() . '/' . $destination_old . '/' . $avatar_id . '.' . $extension,
			'SIZE_X1'			=> $params_size['x1'],
			'SIZE_X2'			=> $params_size['x2'],
			'SIZE_Y1'			=> $params_size['y1'],
			'SIZE_Y2'			=> $params_size['y2'],
			'SIZE_WIDTH'		=> $params_size['w'],
			'SIZE_HEIGHT'		=> $params_size['h'],
			'S_HIDDEN_FIELDS'	=> build_hidden_fields(array('ext' => $extension)),
			'S_CROP_ACTION'		=> $this->helper->route("bb3mobi_AvatarUpload_crop", array('avatar_id' => $avatar_id))
		));

		page_header('Avatar crop');

		$this->template->set_filenames(array(
			'body' => '@bb3mobi_AvatarUpload/crop_body.html')
		);

		page_footer();
	}

	private function resize($params, $destination, $destination_file)
	{
		$avatar_max_width = $this->config['avatar_max_width'];
		$avatar_max_height = $this->config['avatar_max_height'];

		$avatar_width = $params['w'];
		$avatar_height = $params['h'];

		if ($avatar_max_width > $avatar_width || $avatar_max_height > $avatar_height)
		{
			$avatar_max_width = $avatar_width;
			$avatar_max_height = $avatar_height;
		}

		if ($avatar_width > $avatar_max_width)
		{
			$avatar_height = $avatar_height / ($avatar_width / $avatar_max_width);
			$avatar_width = $avatar_max_width;
		}

		if ($avatar_height > $avatar_max_height)
		{
			$avatar_width = $avatar_width / ($avatar_height / $avatar_max_height);
			$avatar_height = $avatar_max_height;
		}

		$destination = imagecreatetruecolor($avatar_width, $avatar_height);

		switch ($params['ext'])
		{
			case 'jpg':
			case 'jpeg':
				$source = imagecreatefromjpeg($destination_file);
			break;

			case 'png':
				$source = imagecreatefrompng($destination_file);
				$color = imagecolorallocatealpha($destination, 0, 0, 0, 127);
				imagefill($destination, 0, 0, $color);
			break;

			case 'gif':
				$source = imagecreatefromgif($destination_file);
			break;
		}

		if (isset($source))
		{
			imagecopyresampled($destination, $source, 0, 0, $params['x1'], $params['y1'], $avatar_width, $avatar_height, $params['w'], $params['h']);
		}

		switch ($params['ext'])
		{
			case 'jpg':
			case 'jpeg':
				imagejpeg($destination, $destination_file, 90);
			break;

			case 'png':
				imagealphablending($destination, true);
				imagesavealpha($destination, true);
				imagepng($destination, $destination_file);
			break;

			case 'gif':
				imagegif($destination, $destination_file);
			break;
		}

		return array(
			'avatar_width'	=> $avatar_width,
			'avatar_height'	=> $avatar_height,
		);
	}
}
