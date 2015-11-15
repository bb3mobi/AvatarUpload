<?php
/**
* avatar_upload [Russian]
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
	'AVATAR_UPLOAD'		=> 'Изменение размеров аватары и обрезка',
	'AVATAR_UPLOAD_EXT'	=> '<a href="http://bb3.mobi">BB3 Mobi</a> (c) Anvar 2015',
	'AVATAR_EXPLAIN2'	=> 'Файл шире %1$s и выше %2$s можно будет изменить в редакторе!',
	'SIZE_X1'			=> 'Отступ слева',
	'SIZE_X2'			=> 'X2',
	'SIZE_Y1'			=> 'Отступ сверху',
	'SIZE_Y2'			=> 'Y2',
	'SIZE_WIDTH'		=> 'Ширина обрезки',
	'SIZE_HEIGHT'		=> 'Высота обрезки',
	'FADETOG'			=> 'Плавная смена эффектов',
	'SHADETOG'			=> 'Экспериментальный шейдер',
	'BGC_BUTTONS'		=> 'Изменить цвет затемнения',
	'BGO_BUTTONS'		=> 'Эффект затемнения',
	'ANIM_BUTTONS'		=> 'Автоматический выбор области',
	'NO_AVATAR_USER'	=> 'Изображения аватары Вам не принадлежит',
	'NO_AVATAR_FILES'	=> 'У Вас нет загруженного изображения аватары',
	'NO_AVATAR_SIZE'	=> 'Запутался в цифрах',
	'ERROR_AVATAR_X2'	=> 'Ширина "X2" больше ширины изображения.',
	'ERROR_AVATAR_Y2'	=> 'Высота "Y2" больше высоты изображения.',
	'ERROR_AVATAR_W'	=> 'Слишком узкая выбранная область',
	'ERROR_AVATAR_H'	=> 'Слишком низкая выбранная область',
));
