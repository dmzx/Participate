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

use dmzx\participate\core\settings;
use phpbb\auth\auth;
use phpbb\db\driver\driver_interface;
use phpbb\exception\http_exception;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

class controller
{
	/** @var driver_interface */
	protected $db;

	/** @var user */
	protected $user;

	/** @var auth */
	protected $auth;

	/** @var request */
	protected $request;

	/** @var language */
	protected $language;

	/** @var settings */
	protected $settings;

	/** @var string */
	protected $root_path;

	/** @var string */
	protected $php_ext;

	/** @var string */
	protected $tables;

	/**
	 * @param driver_interface	$db
	 * @param user				$user
	 * @param auth				$auth
	 * @param request			$request
	 * @param language			$language
	 * @param settings			$settings
	 * @param string			$root_path
	 * @param string			$php_ext
	 * @param string			$tables
	 */
	public function __construct(
		driver_interface $db,
		user $user,
		auth $auth,
		request $request,
		language $language,
		settings $settings,
		$root_path,
		$php_ext,
		$tables
	)
	{
		$this->db			= $db;
		$this->user			= $user;
		$this->auth			= $auth;
		$this->request		= $request;
		$this->language		= $language;
		$this->settings		= $settings;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->tables		= $tables;
	}

	/**
	 * @return JsonResponse|RedirectResponse
	 */
	public function handle()
	{
		$topic_id = $this->request->variable('t', 0);
		$user_id = (int) $this->user->data['user_id'];

		$this->language->add_lang('participate', 'dmzx/participate');

		if (!$this->settings->is_enabled() || !$topic_id)
		{
			throw new http_exception(404, 'NO_TOPIC');
		}

		if (!$this->user->data['is_registered'])
		{
			throw new http_exception(401, 'LOGIN_REQUIRED');
		}

		if (!check_link_hash($this->request->variable('hash', ''), 'participate'))
		{
			throw new http_exception(403, 'FORM_INVALID');
		}

		$sql = 'SELECT forum_id FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$forum_id = (int) $this->db->sql_fetchfield('forum_id');
		$this->db->sql_freeresult($result);

		if (!$forum_id)
		{
			throw new http_exception(404, 'NO_TOPIC');
		}

		if (!$this->settings->is_forum_included($forum_id) || !$this->auth->acl_get('f_reply', $forum_id))
		{
			throw new http_exception(403, 'NOT_AUTHORISED');
		}

		$sql = 'SELECT active FROM ' . $this->tables . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($row === false)
		{
			$active = 1;
			$sql_ary = [
				'user_id'	=> $user_id,
				'topic_id'	=> (int) $topic_id,
				'active'	=> $active,
				'post_time'	=> time(),
			];
			$this->db->sql_query('INSERT INTO ' . $this->tables . ' ' . $this->db->sql_build_array('INSERT', $sql_ary));
		}
		else
		{
			$active = (int) !$row['active'];
			$sql_ary = [
				'active'	=> $active,
				'post_time'	=> time(),
			];
			$this->db->sql_query('UPDATE ' . $this->tables . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . '
				WHERE topic_id = ' . (int) $topic_id . '
					AND user_id = ' . (int) $user_id);
		}

		if (!$this->request->is_ajax())
		{
			return new RedirectResponse(append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 't=' . (int) $topic_id, false));
		}

		return new JsonResponse([
			'success'						=> true,
			'DMZX_PARTICIPATE_STATUS_CLASS'	=> $active ? 'btn-green' : 'btn-red',
			'DMZX_PARTICIPATE_TXT'			=> $active ? $this->language->lang('STATUS_TXT_PARTICIPATE') : $this->language->lang('STATUS_TXT_CANCEL_PARTICIPATE'),
			'DMZX_PARTICIPATE_BUTTON_TXT'	=> $active ? $this->language->lang('STATUS_TITLE_PARTICIPATE') : $this->language->lang('STATUS_TITLE_CANCEL_PARTICIPATE'),
			'PARTICIPANTSBAR'				=> $this->participants_bar($topic_id),
		]);
	}

	/**
	 * @param int $topic_id
	 * @return string
	 */
	protected function participants_bar($topic_id)
	{
		$sql = 'SELECT u.user_id, u.username, u.user_colour, d.active
			FROM ' . $this->tables . ' d
			LEFT JOIN ' . USERS_TABLE . ' u ON (d.user_id = u.user_id)
			WHERE d.topic_id = ' . (int) $topic_id . '
			ORDER BY d.active DESC, d.post_time ASC';
		$result = $this->db->sql_query($sql);

		$names = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$names[] = '<span class="' . ($row['active'] ? 'btn-black' : 'btn-grey strikethrough') . '">'
				. get_username_string('full', $row['user_id'], $row['username'], $row['user_colour'])
				. '</span>';
		}
		$this->db->sql_freeresult($result);

		if (!$names)
		{
			return $this->language->lang('NO_PARTICIPANTS');
		}

		return '<i class="fa fa-info-circle" title="' . $this->language->lang('PARTICIPANTS') . '"></i> '
			. $this->language->lang('PARTICIPANTS') . $this->language->lang('COLON') . ' '
			. implode(', ', $names);
	}
}
