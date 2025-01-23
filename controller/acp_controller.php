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

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log_interface;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use phpbb\config\db_text;
use phpbb\db\driver\driver_interface;

class acp_controller
{
	protected $config;
	protected $language;
	protected $log;
	protected $request;
	protected $template;
	protected $user;
	protected $config_text;
	protected $db;
	protected $tables;

	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param config				$config
	 * @param language 				$language
	 * @param log_interface 		$log,
	 * @param request				$request
	 * @param template				$template
	 * @param user					$user
	 * @param db_text				$config_text
	 */
	public function __construct(
		config $config,
		language $language,
		log_interface $log,
		request $request,
		template $template,
		user $user,
		db_text $config_text,
		driver_interface $db,
		$tables
	)
	{
		$this->config		= $config;
		$this->language		= $language;
		$this->log			= $log;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->config_text 	= $config_text;
		$this->db 			= $db;
		$this->tables		= $tables;
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options()
	{
		// Add our ACP language file
		$this->language->add_lang('acp_participate', 'dmzx/participate');

		// Create a form key for preventing CSRF attacks
		add_form_key('dmzx_participate_acp');

		$data = $this->config_text->get_array([
			'participate_forum_ids',
		]);

		// Get the excluded forums
		$included_forums = explode(',', $data['participate_forum_ids']);

		// Create an array to collect errors that will be output to the user
		$errors = [];

		// Handle form submission
		if ($this->request->is_set_post('submit'))
		{
			// Validate form key
			if (!check_form_key('dmzx_participate_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Save the configured options
				$this->config->set('participate_enable', $this->request->variable('participate_enable', ''));

				$forums = $this->request->variable('selectForms', ['']);
				$forums = implode(',', $forums); // Convert array to a string

				$this->config_text->set_array([
					'participate_forum_ids' => $forums,
				]);

				// Log the changes
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DMZX_LOG_ACP_PARTICIPATE_SETTINGS');

				// Confirm success and redirect
				trigger_error($this->language->lang('DMZX_ACP_PARTICIPATE_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		// Handle reset action
		if ($this->request->is_set_post('reset'))
		{
			// Validate form key
			if (!check_form_key('dmzx_participate_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			// Check if the confirmation checkbox is checked
			elseif (!$this->request->is_set_post('confirm_reset'))
			{
				$errors[] = $this->language->lang('DMZX_ACP_CONFIRM_RESET_REQUIRED');
			}
			else
			{
				// Truncate the `participate` table
				$this->db->sql_query('TRUNCATE TABLE ' . $this->tables['participate']);

				// Log the reset action
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DMZX_LOG_ACP_PARTICIPATE_TABLE_RESET');

				// Confirm success and redirect
				trigger_error($this->language->lang('DMZX_ACP_PARTICIPATE_TABLE_RESET_SUCCESS') . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'						=> $s_errors,
			'ERROR_MSG'					=> $s_errors ? implode('<br>', $errors) : '',
			'PARTICIPATE_ENABLE'			=> $this->config['participate_enable'],
			'PARTICIPATE_VERSION'			=> $this->config['participate_version'],
			'PARTICIPATE_FORUM_INCLUDED'	=> $this->forum_select($included_forums),
			'U_ACTION'					 => $this->u_action,
		]);
	}

	private function forum_select($value)
	{
		return '<select id="participate_forum_ids" name="selectForms[]" multiple="multiple" size="10">' . make_forum_select($value, false, true, true) . '</select>';
	}

	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
