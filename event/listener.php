<?php

namespace nicebb\metarobots\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{

	const ENABLE = 1;
	const DISABLE = -1;

	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_board_config_edit_add' => 'acp_board_config',
			'core.acp_manage_forums_request_data' => 'acp_forums_request_data',
			'core.acp_manage_forums_display_form' => 'acp_forums_display_form',
			'core.viewforum_get_topic_data' => 'check_forum',
			'core.viewtopic_assign_template_vars_before' => 'check_forum',
			'core.page_footer' => 'set_template_variables',
		);
	}

	/* @var \phpbb\config\config */

	protected $config;

	/* @var \phpbb\template\template */
	protected $template;

	/* @var \phpbb\request\request */
	protected $request;
	
	/* @var \phpbb\user */
	protected $user;
	
	private $indexing_enabled;
	private $following_enabled;

	public function __construct(\phpbb\config\config $config, \phpbb\template\template $template, \phpbb\request\request $request, \phpbb\user $user)
	{
		$this->config = $config;
		$this->template = $template;
		$this->request = $request;
		$this->user = $user;
		$this->indexing_enabled = (int) $config['metarobots_indexing_enable'];
		$this->following_enabled = (int) $config['metarobots_following_enable'];
	}

	public function acp_board_config($event)
	{
		$this->user->add_lang_ext('nicebb/metarobots', 'acp/metarobots');

		// Add our variables on the board settings ACP page
		if ($event['mode'] === 'settings')
		{
			$vars = array(
				'legend_metarobots' => 'ACP_METAROBOTS_SETTINGS',
				'metarobots_indexing_enable' => array(
					'lang' => 'METAROBOTS_INDEXING_ENABLE',
					'validate' => 'bool',
					'type' => 'radio:yes_no',
					'explain' => true,
				),
				'metarobots_following_enable' => array(
					'lang' => 'METAROBOTS_FOLLOWING_ENABLE',
					'validate' => 'bool',
					'type' => 'radio:yes_no',
					'explain' => true,
				),
			);
			$display_vars = $event['display_vars'];
			$display_vars['vars'] = $this->assoc_array_insert_before($display_vars['vars'], 'legend4', $vars);
			$event['display_vars'] = $display_vars;
		}
	}

	public function acp_forums_request_data($event)
	{
		if (in_array($event['action'], array('add', 'edit')))
		{
			$forum_data = $event['forum_data'];
			$forum_data['metarobots_indexing_enable'] = $this->request->variable('metarobots_indexing_enable', 0);
			$forum_data['metarobots_following_enable'] = $this->request->variable('metarobots_following_enable', 0);
			$event['forum_data'] = $forum_data;
		}
	}

	public function acp_forums_display_form($event)
	{
		$this->user->add_lang_ext('nicebb/metarobots', 'acp/metarobots');
		$template_data = $event['template_data'];
		$keys = array('metarobots_indexing_enable', 'metarobots_following_enable');
		foreach ($keys as $key)
		{
			$template_data[strtoupper($key)] = isset($event['forum_data'][$key]) ? $event['forum_data'][$key] : 0;
		}
		$event['template_data'] = $template_data;
	}

	public function check_forum($event)
	{
		$data = empty($event['topic_data']) ? $event['forum_data'] : $event['topic_data'];
		if ($data['metarobots_indexing_enable'] == self::ENABLE)
		{
			$this->indexing_enabled = true;
		}
		else if ($data['metarobots_indexing_enable'] == self::DISABLE)
		{
			$this->indexing_enabled = false;
		}

		if ($data['metarobots_following_enable'] == self::ENABLE)
		{
			$this->following_enabled = true;
		}
		else if ($data['metarobots_following_enable'] == self::DISABLE)
		{
			$this->following_enabled = false;
		}
	}

	public function set_template_variables()
	{
		$this->template->assign_vars(array(
			'METAROBOTS_INDEXING_ENABLE' => $this->indexing_enabled,
			'METAROBOTS_FOLLOWING_ENABLE' => $this->following_enabled,
		));
	}

	private function assoc_array_insert_before($array, $position, $value)
	{
		if (!isset($array[$position]))
		{
			$array = array_merge($array, $value);
		}
		else
		{
			$index = array_search($position, array_keys($array));
			$array = array_merge(
					array_slice($array, 0, $index), $value, array_slice($array, $index)
			);
		}
		return $array;
	}

}
