<?php
/**
 *
 * Participate. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\participate\migrations;

class install_participate extends \phpbb\db\migration\migration
{
	public function update_schema()
	{
		return [
			'add_tables'	=> [
			$this->table_prefix . 'participate' => [
				'COLUMNS'		=> [
					'user_id'	=> ['UINT:11', 0],
					'topic_id'	=> ['UINT:11', 0],
					'active'	=> ['BOOL', 1],
					'post_time' => ['UINT:11', 0],
				],
				'KEYS'			=> [
					'id'		=> ['UNIQUE', ['user_id', 'topic_id']]]
				]
			]
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['participate_enable', 0]],
			['config.add', ['participate_version', '1.0.0']],

			['config_text.add', ['participate_forum_ids', '']],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PARTICIPANTS_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_PARTICIPANTS_TITLE',
				[
					'module_basename'	=> '\dmzx\participate\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_tables' => [
				$this->table_prefix . 'participate'
		]];
	}
}
