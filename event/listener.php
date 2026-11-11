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

use dmzx\participate\core\settings;
use phpbb\db\driver\driver_interface;
use phpbb\controller\helper;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\auth\auth;
use phpbb\extension\manager;
use phpbb\language\language;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	protected $db;
	protected $settings;
	protected $controller_helper;
	protected $request;
	protected $template;
	protected $user;
	protected $auth;
	protected $extension_manager;
	protected $language;
	protected $tables;

	public function __construct(
		driver_interface $db,
		settings $settings,
		helper $controller_helper,
		request $request,
		template $template,
		user $user,
		auth $auth,
		manager $extension_manager,
		language $language,
		$tables
	)
	{
		$this->db						= $db;
		$this->settings					= $settings;
		$this->controller_helper		= $controller_helper;
		$this->request					= $request;
		$this->template					= $template;
		$this->user						= $user;
		$this->auth						= $auth;
		$this->extension_manager		= $extension_manager;
		$this->language					= $language;
		$this->tables					= $tables;

		$this->language->add_lang('participate', 'dmzx/participate');
	}

	static public function getSubscribedEvents()
	{
		return [
			'core.viewtopic_modify_post_data'	=> 'viewtopic_modify_post_data',
		];
	}

	public function viewtopic_modify_post_data($event)
	{
		if ($this->settings->is_enabled())
		{
			$data = $event['topic_data'];

			if ($this->settings->is_forum_included($data['forum_id']))
			{
				$sql = 'SELECT active
						FROM ' . $this->tables . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
						AND topic_id = ' . (int) $data['topic_id'];
				$result = $this->db->sql_query($sql);
				$row = $this->db->sql_fetchrow($result);
				$this->db->sql_freeresult($result);

				$this->template->assign_vars([
					'S_PARTICIPATE'					=> $this->user->data['is_registered'] && $this->auth->acl_get('f_reply', $data['forum_id']),
					'DMZX_PARTICIPATE_STATUS_CLASS'	=> (!$row) ? 'btn-grey' : (($row['active']) ? 'btn-green' : 'btn-red'),
					'DMZX_PARTICIPATE_TXT' 			=> (!$row) ? $this->language->lang('STATUS_TXT_NOT_PARTICIPATE') : (($row['active']) ? $this->language->lang('STATUS_TXT_PARTICIPATE') : $this->language->lang('STATUS_TXT_CANCEL_PARTICIPATE')),
					'DMZX_PARTICIPATE_BUTTON_TXT' 	=> (!$row) ? $this->language->lang('STATUS_TITLE_NOT_PARTICIPATE') : (($row['active']) ? $this->language->lang('STATUS_TITLE_PARTICIPATE') : $this->language->lang('STATUS_TITLE_CANCEL_PARTICIPATE')),
					'DMZX_PARTICIPATE_STATUS_URL' 	=> $this->controller_helper->route('dmzx_participate_controller', ['t' => $data['topic_id'], 'hash' => generate_link_hash('participate')]),
					'DMZX_PARTICIPATE_INFO_URL' 	=> '<i class="fa fa-info-circle" title="' . $this->language->lang('PARTICIPANTS') . '"></i> ',
				]);

				$sql = 'SELECT u.user_id, u.username, u.user_colour, d.active
						FROM ' . $this->tables . ' AS d
						LEFT JOIN ' . USERS_TABLE . ' AS u ON (d.user_id = u.user_id)
						WHERE d.topic_id = ' . (int) $data['topic_id'] . '
						ORDER BY d.active DESC, d.post_time ASC';
				$result = $this->db->sql_query($sql);

				while ($row = $this->db->sql_fetchrow($result))
				{
					$this->template->assign_block_vars('participants', [
						'USERNAME'	=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
						'CLASS'		=> ($row['active']) ? 'btn-black' : 'btn-grey strikethrough',
					]);
				}
				$this->db->sql_freeresult($result);
			}
			$this->assign_authors();
		}
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
