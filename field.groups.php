<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Streams Groups Field Type
 *
 * @package		PyroCMS\Addons\Shared Addons\Field Types
 */
class Field_groups
{
	public $field_type_name				= 'Groups';

	public $field_type_slug				= 'groups';

	public $db_col_type					= 'varchar';

	public $version						= '1.0';
	
	public $author						= array('name'=>'Ryan Thompson', 'url'=>'http://aiwebsystems.com');

	// --------------------------------------------------------------------------
	
	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->model('groups/group_m');
	}

	// --------------------------------------------------------------------------

	public function form_output($data)
	{
		$group_options 		= $this->make_dropdown();

		$options['name'] 	= $data['form_slug'].'[]';
		$options['id']		= $data['form_slug'];
		$options['value']	= array(0 => lang('global:select-any')) + $group_options;
		$option['extra']	= ($count = count($group_options)) > 1 ? $count : 2;

		$data['value']		= isset($data['value']) && ! is_array($data['value']) ? explode('*', substr($data['value'], 1, -1)) : NULL;

		return form_multiselect($data['form_slug'].'[]', $options['value'], $data['value'], $option['extra']);
	}

	public function pre_save($input = array())
	{
		return '*'.implode('*', $input).'*';
	}

	public function pre_output($input = '')
	{
		if($input == '*0*')
		{
			return lang('global:select-all');
		}

		$ids = explode('*', substr($input, 1, -1));

		$groups = $this->CI->db
				->where_in('id', $ids)
				->get('groups')
				->result();

		$return = array();
		foreach($groups as $group)
		{
			if(in_array($group->id, $ids))
			{
				$return[$group->id] = '<li>'.$group->description.'</li>';
			}
		}

		return '<ul>'.implode("\r\n",$return).'</ul>';
	}

	public function pre_output_plugin($input = '')
	{
		return explode('*', substr($input, 1, -1));
	}

	private function make_dropdown()
	{
		$groups = $this->CI->group_m->get_all();
    
		foreach ($groups as $group)
		{
			// Skip admins for now
			if ($group->name == 'admin') continue;
			
			// Form dropdown
			$group_options[$group->id] = $group->name;
		}

		return $group_options;
	}
}
