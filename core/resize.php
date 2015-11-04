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

	protected $phpbb_root_path;

	protected $php_ext;

	public function __construct(\phpbb\config\config $config, \phpbb\user $user, $phpbb_root_path, $php_ext)
	{
		$this->config = $config;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	/**
	* @var \phpbb\mimetype\guesser
	*/
	protected $mimetype_guesser;
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

	public function avatar_upload_resize($row)
	{
		if (!class_exists('fileupload'))
		{
			include($this->phpbb_root_path . 'includes/functions_upload.' . $this->php_ext);
		}

		$upload = new \fileupload('AVATAR_', $this->allowed_extensions, $this->config['avatar_filesize'], $this->config['avatar_min_width'], $this->config['avatar_min_height'], 2400, 2400, (isset($this->config['mime_triggers']) ? explode('|', $this->config['mime_triggers']) : false));

		$file = $upload->form_upload('avatar_upload_file', $this->mimetype_guesser);

		$avatar_max_width = $this->config['avatar_max_width'];
		$avatar_max_height = $this->config['avatar_max_height'];

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

		$destination_file = $this->phpbb_root_path . $destination . '/' . $prefix . $row['id'] . '.' . $file->get('extension');

		$file->move_file($destination, true);

		if (sizeof($file->error))
		{
			$file->remove();
			trigger_error(implode('<br />', $file->error));
		}
		else
		{
			rename($this->phpbb_root_path . $destination . '/' . utf8_basename($file->realname), $destination_file);
		}

		// Delete current avatar if not overwritten
		$ext = substr(strrchr($row['avatar'], '.'), 1);
		if ($ext && $ext !== $file->get('extension'))
		{
			$this->delete($row);
		}

		$avatar_width = $file->width;
		$avatar_height = $file->height;

		if ($avatar_width > $avatar_max_width || $avatar_height > $avatar_max_height)
		{
			if ($avatar_height > $avatar_max_height OR $avatar_width > $avatar_max_width)
			{
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

				switch ($file->extension)
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
					imagecopyresampled($destination, $source, 0, 0, 0, 0, $avatar_width, $avatar_height, $file->width, $file->height);
				}

				switch ($file->extension)
				{
					case 'jpg':
					case 'jpeg':
						imagejpeg($destination, $destination_file);
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
			}
		}

		return array(
			'avatar'		=> $row['id'] . '_' . time() . '.' . $file->get('extension'),
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
