<?php

/**
*
* @package Resize upload avatar
* @copyright bb3.mobi 2015 (c) Anvar (resspect.ru)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*/

namespace bb3mobi\AvatarUpload\core;

class resize
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\mimetype\guesser */
	protected $mimetype_guesser;

	/** @var \phpbb\controller\helper */
	protected $helper;

	protected $phpbb_root_path;

	protected $php_ext;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user, \phpbb\mimetype\guesser $mimetype_guesser, \phpbb\controller\helper $helper, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->user = $user;
		$this->mimetype_guesser = $mimetype_guesser;
		$this->helper = $helper;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}
	/**
	* Array of allowed avatar image extensions
	* Array is used for setting the allowed extensions in the fileupload class
	* and as a base for a regex of allowed extensions, which will be formed by
	* imploding the array with a "|".
	*
	* @var array
	*/
	protected $allowed_extensions = array(
		'gif',
		'jpg',
		'jpeg',
		'png',
	);

	/** Path to edit file */
	private $d_edit = 'ext/bb3mobi/AvatarUpload/images';

	/** Max size to display */
	private $max_size = 900;

	public function avatar_explain()
	{
		global $phpbb_container;

		$context = $phpbb_container->get('template_context');
		$this->tpldata = $context->get_data_ref();

		if (isset($this->tpldata['.'][0]['L_AVATAR_EXPLAIN']))
		{
			$this->user->add_lang_ext('bb3mobi/AvatarUpload', 'avatar_upload');

			$avatar_explain = $this->user->lang('AVATAR_EXPLAIN', $this->user->lang('PIXELS', (int) $this->config['avatar_upload_max_width']), $this->user->lang('PIXELS', (int) $this->config['avatar_upload_max_height']), round($this->config['avatar_filesize'] / 1024));

			$avatar_explain2 = $this->user->lang('AVATAR_EXPLAIN2', $this->user->lang('PIXELS', (int) $this->config['avatar_max_width']), $this->user->lang('PIXELS', (int) $this->config['avatar_max_height']));

			$this->tpldata['.'][0]['L_AVATAR_EXPLAIN'] = $avatar_explain . '<p class="error">' . $avatar_explain2 . '</p>';
		}
	}

	public function avatar_upload_resize($row)
	{
		if (!class_exists('fileupload'))
		{
			include($this->phpbb_root_path . 'includes/functions_upload.' . $this->php_ext);
		}

		$upload = new \fileupload('AVATAR_', $this->allowed_extensions, $this->config['avatar_filesize'], $this->config['avatar_min_width'], $this->config['avatar_min_height'],  $this->config['avatar_upload_max_width'], $this->config['avatar_upload_max_height'], (isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

		$file = $upload->form_upload('avatar_upload_file', $this->mimetype_guesser);

		$prefix = $this->config['avatar_salt'] . '_';
		$file->clean_filename('avatar', $prefix, $row['id']);

		// If there was an error during upload, then abort operation
		if (sizeof($file->error))
		{
			$file->remove();
			$error = $file->error;
			return false;
		}

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

		$destination_file = $this->phpbb_root_path . $destination . '/' . $prefix . $row['id'] . '.' . $file->get('extension');

		$file->move_file($destination, true);

		if (sizeof($file->error))
		{
			$file->remove();
			trigger_error(implode('<br />', $file->error));
		}

		// Delete current avatar if not overwritten
		$ext = substr(strrchr($row['avatar'], '.'), 1);
		if ($ext && $ext !== $file->get('extension'))
		{
			$this->delete($row);
		}

		if ($file->width > $this->max_size || $file->height > $this->max_size)
		{
			$avatar_info = $this->resize(array(
				'w' => $file->width,
				'h' => $file->height,
				'ext' => $file->extension),
				$destination, $destination_file);

			/** New file width & height */
			$file->width = $avatar_info['avatar_width'];
			$file->height = $avatar_info['avatar_height'];
		}

		if ($file->width > $this->config['avatar_max_width'] || $file->height > $this->config['avatar_max_height'])
		{
			$destination_edit_file = $this->phpbb_root_path . $this->d_edit . '/' . $row['id'] . '.' . $file->get('extension');
			rename($destination_file, $destination_edit_file);
			redirect($this->helper->route("bb3mobi_AvatarUpload_crop", array('avatar_id' => $row['id'], 'ext' => $file->extension)), false, true);
		}

		return array(
			'avatar'		=> $row['id'] . '_' . time() . '.' . $file->get('extension'),
			'avatar_width'	=> $file->width,
			'avatar_height'	=> $file->height,
		);
	}

	private function resize($params, $destination, $destination_file)
	{
		$avatar_max_width = $this->max_size;
		$avatar_max_height = $this->max_size;

		$avatar_width = $params['w'];
		$avatar_height = $params['h'];

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
			imagecopyresampled($destination, $source, 0, 0, 0, 0, $avatar_width, $avatar_height, $params['w'], $params['h']);
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

	/**
	* {@inheritdoc}
	*/
	private function delete($row)
	{
		$destination = $this->config['avatar_path'];
		$prefix = $this->config['avatar_salt'] . '_';
		$ext = substr(strrchr($row['avatar'], '.'), 1);
		$filename = $this->phpbb_root_path . $destination . '/' . $prefix . $row['id'] . '.' . $ext;

		if (file_exists($filename))
		{
			@unlink($filename);
		}

		return true;
	}
}
