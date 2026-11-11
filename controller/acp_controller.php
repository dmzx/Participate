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
use phpbb\extension\manager;

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
	protected $ext_manager;
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
	 * @param driver_interface		$db
	 * @param manager				$ext_manager
	 * @param string				$tables
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
		manager $ext_manager,
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
		$this->ext_manager	= $ext_manager;
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
				$this->config->set('participate_enable', $this->request->variable('participate_enable', 0));

				$forums = $this->request->variable('selectForms', [0]);
				$forums = implode(',', array_map('intval', $forums));

				$this->config_text->set_array([
					'participate_forum_ids' => $forums,
				]);

				// Log the changes
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DMZX_LOG_ACP_PARTICIPATE_SETTINGS');

				// Confirm success and redirect
				trigger_error($this->language->lang('DMZX_ACP_PARTICIPATE_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		if ($this->request->is_set_post('reset'))
		{
			if (confirm_box(true))
			{
				$this->db->sql_query('DELETE FROM ' . $this->tables);

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'DMZX_LOG_ACP_PARTICIPATE_TABLE_RESET');

				trigger_error($this->language->lang('DMZX_ACP_PARTICIPATE_TABLE_RESET_SUCCESS') . adm_back_link($this->u_action));
			}

			if (!check_form_key('dmzx_participate_acp'))
			{
				trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
			}

			confirm_box(false, $this->language->lang('DMZX_ACP_ARE_YOU_SURE'), build_hidden_fields([
				'reset'	=> 1,
			]));
		}

		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'						=> $s_errors,
			'ERROR_MSG'						=> $s_errors ? implode('<br>', $errors) : '',
			'PARTICIPATE_ENABLE'			=> (bool) $this->config['participate_enable'],
			'PARTICIPATE_VERSION'			=> $this->ext_manager->create_extension_metadata_manager('dmzx/participate')->get_metadata('version'),
			'PARTICIPATE_FORUM_INCLUDED'	=> $this->forum_select($included_forums),
			'U_ACTION'					 	=> $this->u_action,
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
