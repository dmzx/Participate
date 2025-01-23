<?php
/**
 *
 * Participate. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\participate\event;

use phpbb\db\driver\driver_interface;
use phpbb\config\config;
use phpbb\config\db_text;
use phpbb\controller\helper;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\auth\auth;
use phpbb\extension\manager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	protected $db;
	protected $config;
	protected $config_text;
	protected $controller_helper;
	protected $request;
	protected $template;
	protected $user;
	protected $auth;
	protected $extension_manager;
	protected $tables;
	protected $participate_include = false;

	public function __construct(
		driver_interface $db,
		config $config,
		db_text $config_text,
		helper $controller_helper,
		request $request,
		template $template,
		user $user,
		auth $auth,
		manager $extension_manager,
		$tables
	)
	{
		$this->db						= $db;
		$this->config					= $config;
		$this->config_text 				= $config_text;
		$this->controller_helper		= $controller_helper;
		$this->request					= $request;
		$this->template					= $template;
		$this->user						= $user;
		$this->auth						= $auth;
		$this->extension_manager		= $extension_manager;
		$this->tables					= $tables;

		$this->user->add_lang_ext('dmzx/participate', 'participate');
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.viewtopic_modify_post_data'	=> 'viewtopic_modify_post_data',
		];
	}

	public function viewtopic_modify_post_data($event)
	{
		if ($this->config['participate_enable'])
		{
			$post_id 	= $event['post_id'];
			$data 		= $event['topic_data'];

			if ($this->is_forum_included($data['forum_id']) == true)
			{
				$this->participate_include = true;
			}

			if ($this->participate_include && $post_id = $data['topic_first_post_id'])
			{
				$sql = 'SELECT active
						FROM ' . $this->tables['participate'] . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
						AND topic_id = ' . (int) $data['topic_id'];
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);

				$this->template->assign_vars([
					'S_PARTICIPATE'					=> ($this->auth->acl_get('f_reply', $data['forum_id']) && $post_id == $data['topic_first_post_id']),
					'DMZX_PARTICIPATE_STATUS_CLASS'	=> (!$row) ? 'btn-grey' : (($row['active']) ? 'btn-green' : 'btn-red'),
					'DMZX_PARTICIPATE_TXT' 			=> (!$row) ? $this->user->lang['STATUS_TXT_NOT_PARTICIPATE'] : (($row['active']) ? $this->user->lang['STATUS_TXT_PARTICIPATE'] : $this->user->lang['STATUS_TXT_CANCEL_PARTICIPATE']),
					'DMZX_PARTICIPATE_BUTTON_TXT' 	=> (!$row) ? $this->user->lang['STATUS_TITLE_NOT_PARTICIPATE'] : (($row['active']) ? $this->user->lang['STATUS_TITLE_PARTICIPATE'] : $this->user->lang['STATUS_TITLE_CANCEL_PARTICIPATE']),
					'DMZX_PARTICIPATE_STATUS_URL' 	=> $this->controller_helper->route('dmzx_participate_controller', ['name' => 'index.html', 't' => $data['topic_id']]),
					'DMZX_PARTICIPATE_INFO_URL' 	=> '<i class="fa fa-info-circle" title="' . $this->user->lang['PARTICIPANTS'] . '"></i> ',
				]);

				$sql = 'SELECT u.username, u.user_colour, d.user_id, d.active
						FROM ' . $this->tables['participate'] . ' AS d
						LEFT JOIN ' . USERS_TABLE . ' AS u ON (d.user_id = u.user_id)
						WHERE d.topic_id = ' . (int) $data['topic_id'] . '
						ORDER BY d.active DESC, d.post_time ASC';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('participants', [
						'USERNAME' 		=> $row['username'],
						'USERCOLOUR' 	=> $row['user_colour'],
						'USERID' 		=> $row['user_id'],
						'CLASS' 		=> ($row['active']) ? 'btn-black' : 'btn-grey strikethrough',
					]);
				}
				$this->db->sql_freeresult($result);
			}
			$this->assign_authors();
		}
	}

	private function is_forum_included($forum_id)
	{
		$data = $this->config_text->get_array([
			'participate_forum_ids',
		]);

		$forums_included = preg_split("/[\s,]+/", $data['participate_forum_ids']);

		return in_array($forum_id, $forums_included);
	}

	protected function assign_authors()
	{
		$md_manager = $this->extension_manager->create_extension_metadata_manager('dmzx/participate');
		$meta = $md_manager->get_metadata();

		$author_homepages = [];

		foreach (array_slice($meta['authors'], 0, 1) as $author)
		{
			$author_homepages[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', $author['homepage'], $author['name']);
		}

		$this->template->assign_vars([
			'PARTICIPATE_DISPLAY_NAME'		=> $meta['extra']['display-name'],
			'PARTICIPATE_AUTHOR_HOMEPAGES'	=> implode(' &amp; ', $author_homepages),
		]);
	}
}
