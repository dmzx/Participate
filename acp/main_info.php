<?php
/**
 *
 * Participate. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\participate\acp;

class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\dmzx\participate\acp\main_module',
			'title'		=> 'ACP_PARTICIPANTS_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_PARTICIPANTS',
					'auth'	=> 'ext_dmzx/participate && acl_a_board',
					'cat'	=> ['ACP_PARTICIPANTS_TITLE']
				],
			],
		];
	}
}
