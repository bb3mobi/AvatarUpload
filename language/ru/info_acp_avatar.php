<?php
/**
*
* Upload Avatar [Russian]
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
	'MAX_FILESIZE_UPLOAD'			=> 'Максимальный размер загружаемой аватары:',
	'MAX_FILESIZE_UPLOAD_EXPLAIN'	=> 'Максимальный размер аватары для прямой загрузки, редактирования и обрезки. Если значение равно 0, размер файла ограничен только конфигурацией PHP.',
	'MAX_AVATAR_UPLOAD'				=> 'Максимальные размеры загружаемых аватар:',
	'MAX_AVATAR_UPLOAD_EXPLAIN'		=> 'Максимальный размеры аватары для прямой загрузки, редактирования и обрезки. Ширина × высота (в пикселах).',
));
