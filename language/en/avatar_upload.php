<?php
/**
* avatar_upload [English]
*
* @package Avatar Upload
* @version $Id: avatar_upload.php, v 1.0.0
* @copyright BB3.Mobi 2015 (c) Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'AVATAR_UPLOAD'		=> 'Avatar Upload - Resize & Crop',
	'AVATAR_UPLOAD_EXT'	=> '<a href="http://bb3.mobi">Support Extension</a> (c) Anvar 2015',
	'AVATAR_EXPLAIN2'	=> 'File broader %1$s and above %2$s can be changed in the editor!',
	'SIZE_X1'			=> 'X1',
	'SIZE_X2'			=> 'X2',
	'SIZE_Y1'			=> 'Y1',
	'SIZE_Y2'			=> 'Y2',
	'SIZE_WIDTH'		=> 'W',
	'SIZE_HEIGHT'		=> 'H',
	'FADETOG'			=> 'Enable fading (bgFade: true)',
	'SHADETOG'			=> 'Use experimental shader (shade: true)',
	'BGC_BUTTONS'		=> 'Change bgColor',
	'BGO_BUTTONS'		=> 'Change bgOpacity',
	'ANIM_BUTTONS'		=> 'Animate Selection',
	'NO_AVATAR_USER'	=> 'No user avatar',
	'NO_AVATAR_FILES'	=> 'No file avatar',
	'NO_AVATAR_SIZE'	=> 'No size avatar',
	'ERROR_AVATAR_X2'	=> 'Width "X2" above width of image.',
	'ERROR_AVATAR_Y2'	=> 'Height "X2" above height of image.',
	'ERROR_AVATAR_W'	=> 'Too narrow area',
	'ERROR_AVATAR_H'	=> 'Too low area',
));
