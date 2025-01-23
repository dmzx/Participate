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
	'PARTICIPANTS'						=> 'Participants for this event',
	'NO_PARTICIPANTS'					=> 'No Participants for this event',
	'STATUS_TXT_NOT_PARTICIPATE'		=> 'You have not signed up',
	'STATUS_TXT_PARTICIPATE'			=> 'You have signed up',
	'STATUS_TXT_CANCEL_PARTICIPATE'		=> 'Unsubscribed',
	'STATUS_TITLE_NOT_PARTICIPATE'		=> 'Sign me in directly',
	'STATUS_TITLE_PARTICIPATE'			=> 'I unsubscribe again',
	'STATUS_TITLE_CANCEL_PARTICIPATE'	=> 'Sign me in again'
]);
