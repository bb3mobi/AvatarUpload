<?php
/**
*
* Upload Avatar [English]
*
* @package info_acp_avatar.php
* @copyright BB3.Mobi 2015 (c) Anvar (apwa.ru)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
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
	'MAX_FILESIZE_UPLOAD'			=> 'Maximum size of a downloadable avatar:',
	'MAX_FILESIZE_UPLOAD_EXPLAIN'	=> 'Maximum size of avatars for direct upload, edit and trim. If this value is 0, the uploaded filesize is only limited by your PHP configuration.',
	'MAX_AVATAR_UPLOAD'				=> 'Maximum size of uploaded avatars:',
	'MAX_AVATAR_UPLOAD_EXPLAIN'		=> 'Maximum size of avatars for direct upload, edit and trim. Width × height (in pixels).',
));
