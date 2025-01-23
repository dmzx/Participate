<?php
/**
 *
 * Participate. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\participate\controller;

use phpbb\db\driver\driver_interface;
use phpbb\template\template;
use phpbb\user;
use phpbb\request\request;
use phpbb\controller\helper;

class controller
{
	protected $db;
	protected $template;
	protected $user;
	protected $helper;
	protected $request;
	protected $tables;

	public function __construct(
		driver_interface $db,
		template $template,
		user $user,
		request $request,
		helper $helper,
		$tables
	)
	{
		$this->db 			= $db;
		$this->template		= $template;
		$this->user			= $user;
		$this->request		= $request;
		$this->helper		= $helper;
		$this->tables		= $tables;
	}

	public function handle($name)
	{
		switch ($name)
		{
			default:
				$topicid = $this->request->variable('t', 0);

				if ($topicid)
				{
					$this->user->add_lang_ext('dmzx/participate', 'participate');

					$sql = 'SELECT user_id, active FROM ' . $this->tables['participate'] . ' WHERE topic_id = ' . (int) $topicid . ' AND user_id = ' . (int) $this->user->data['user_id'];
					$result = $this->db->sql_query($sql);
					$row = $this->db->sql_fetchrow($result);

					if ($row === false || !isset($row['user_id']))
					{
						// No row found, insert a new one
						$sql = 'INSERT INTO ' . $this->tables['participate'] . ' (user_id, topic_id, active, post_time)
								VALUES (' . (int) $this->user->data['user_id'] . ', ' . (int) $topicid . ', 1, ' . time() . ')';
						$row['active'] = 1;
					}
					else
					{
						// Row exists, update it
						$sql = 'UPDATE ' . $this->tables['participate'] . '
								SET active = ' . (int)!$row['active'] . ', post_time = ' . time() . '
								WHERE topic_id = ' . (int)$topicid . ' AND user_id = ' . (int) $this->user->data['user_id'];
						$row['active'] = !$row['active'];
					}
					$this->db->sql_query($sql);

					$participants = '';

					$sql = 'SELECT u.username, u.user_colour, d.user_id, d.active
							FROM ' . $this->tables['participate'] . ' AS d
							LEFT JOIN ' . USERS_TABLE . ' AS u ON (d.user_id = u.user_id)
							WHERE d.topic_id = ' . (int) $topicid . '
							ORDER BY d.active DESC, d.post_time ASC';
					$result = $this->db->sql_query($sql);

					while ($row1 = $this->db->sql_fetchrow($result))
					{
						$participants .= (($participants == '') ? $this->user->lang['PARTICIPANTS'] . $this->user->lang['COLON'] . ' ': ', ') . '<span class="' . (($row1['active']) ? 'btn-black' : 'btn-grey strikethrough') . '" style="color: #' . $row1['user_colour'] . ';">' . $row1['username'] . '</span>';
					}

					$info_url = '<i class="fa fa-info-circle" title="' . $this->user->lang['PARTICIPANTS'] . '"></i> ';

					if ($this->request->is_ajax())
					{
						$json_response = new \phpbb\json_response();
						$json_response->send([
							'success'							=> true,
							'DMZX_PARTICIPATE_STATUS_CLASS'		=> ($row['active']) ? 'btn-green' : 'btn-red',
							'DMZX_PARTICIPATE_TXT'				=> ($row['active']) ? $this->user->lang['STATUS_TXT_PARTICIPATE'] : $this->user->lang['STATUS_TXT_CANCEL_PARTICIPATE'],
							'DMZX_PARTICIPATE_BUTTON_TXT'		=> ($row['active']) ? $this->user->lang['STATUS_TITLE_PARTICIPATE'] : $this->user->lang['STATUS_TITLE_CANCEL_PARTICIPATE'],
							'PARTICIPANTSBAR'					=> $info_url . $participants
						]);
					}
				}
			break;
		}
	}
}
