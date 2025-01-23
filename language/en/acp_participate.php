<?php
/**
 *
 * Participate. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'DMZX_ACP_PARTICIPATE_SETTING_SAVED'			=> 'Settings have been saved successfully!',
	'DMZX_ACP_PARTICIPATE_ENABLE'					=> 'Enable participate',
	'DMZX_ACP_PARTICIPATE_ENABLE_EXPLAIN'			=> 'When enabled participate members will shown in viewtopic.',
	'DMZX_ACP_PARTICIPATE_FORUM_INCLUDED'			=> 'Include forums',
	'DMZX_ACP_PARTICIPATE_FORUM_INCLUDED_EXPLAIN'	=> 'Include forums to participate members in topic.',
	'DMZX_ACP_PARTICIPATE_TITLE'					=> 'Participate',
	'DMZX_ACP_PARTICIPATE_VERSION'					=> 'Version',
	'DMZX_ACP_RESET_TABLE'							=> 'Reset Participate Table',
	'DMZX_ACP_RESET_TABLE_EXPLAIN'					=> 'This will delete all data from the participate table. Use with caution!',
	'DMZX_ACP_CONFIRM_RESET_REQUIRED'					=> 'You must confirm that you want to reset the table.',
	'DMZX_ACP_CONFIRM_RESET'			 			=> 'I understand the consequences and want to reset the table.',
	'DMZX_ACP_ARE_YOU_SURE'							=> 'Are you sure you want to reset the participate table? This action cannot be undone.',
	'DMZX_ACP_PARTICIPATE_TABLE_RESET_SUCCESS' 		=> 'The participate table has been successfully reset.',
]);
