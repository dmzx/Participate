<?php
/**
 *
 * Participate. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\participate\core;

use phpbb\config\config;
use phpbb\config\db_text;

class settings
{
	/** @var config */
	protected $config;

	/** @var db_text */
	protected $config_text;

	/** @var array|null */
	protected $forum_ids;

	/**
	 * @param config	$config
	 * @param db_text	$config_text
	 */
	public function __construct(config $config, db_text $config_text)
	{
		$this->config		= $config;
		$this->config_text	= $config_text;
	}

	/**
	 * @return bool
	 */
	public function is_enabled()
	{
		return (bool) $this->config['participate_enable'];
	}

	/**
	 * @return int[]
	 */
	public function get_forum_ids()
	{
		if ($this->forum_ids === null)
		{
			$data = $this->config_text->get_array(['participate_forum_ids']);
			$ids = preg_split('/[\s,]+/', (string) $data['participate_forum_ids'], -1, PREG_SPLIT_NO_EMPTY);

			$this->forum_ids = array_map('intval', $ids);
		}

		return $this->forum_ids;
	}

	/**
	 * @param int $forum_id
	 * @return bool
	 */
	public function is_forum_included($forum_id)
	{
		return in_array((int) $forum_id, $this->get_forum_ids(), true);
	}
}
