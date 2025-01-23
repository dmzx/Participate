<?php
/**
 *
 * Participate. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\participate;

class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		return phpbb_version_compare(PHPBB_VERSION, '3.3.0', '>=');
	}
}