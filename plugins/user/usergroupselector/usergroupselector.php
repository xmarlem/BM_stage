<?php
/**
 * @package 	Plugin Usergroupselector for Joomla! 3.X
 * @version 	0.0.1
 * @author 		Function90.com
 * @copyright 	C) 2013- Function90.com
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
**/

defined('_JEXEC') or die;

class plgUserUsergroupselector extends JPlugin
{
	protected $autoloadLanguage = true;
		
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		$allowed_groups = $this->params->get('allowed_groups');
		if($isnew && $success){
			$input = JFactory::getApplication()->input;
			$requestData  = $input->post->get('jform', array(), 'array');
			if(isset($requestData['usergroupselector']) && in_array($requestData['usergroupselector'], $allowed_groups)){				
				$juser = JFactory::getUser($user['id']);
				$juser->groups = array($requestData['usergroupselector']);
				unset($requestData['usergroupselector']);
				$input->set('jform', $requestData, 'array');
				$juser->save();
			}
		}
	}
	public function onContentPrepareForm($form, $data)
	{
		$app = JFactory::getApplication();
		if($app->isAdmin()){
			return true;
		}
		
		if($form->getName() != 'com_users.registration'){
			return true;
		}
		
		$groups = $this->getJoomlaUserGroups();
		
		$allowed_groups = $this->params->get('allowed_groups');
		
		$xml = "<fieldset name='usergroupselector'>
					<field 
						type='list'
						name='usergroupselector'
						label='".$this->params->get('label')."'
						description='".$this->params->get('desc')."'";
		
		if($this->params->get('required', false)){
			$xml .= ' required="true"';
		}

		$default = $this->params->get('default_group', false);
		if($default != false){
			$xml .= ' default="'.$default.'"';
		}
		
		$xml .= ">";
		
		foreach($groups as $groupid => $group){
			if(in_array($groupid, $allowed_groups)){
				$xml .= "<option value='".$groupid."'><![CDATA[ ".$this->xmlEscape($group->title)." ]]></option>";
			}
		}
						
		$xml .=	"</field>
				</fieldset>
				";
		
		$form->setField(new SimpleXMLElement($xml));				
	}
	
	function xmlEscape($string) 
	{
    	return str_replace(array('&', '<', '>', '\'', '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
	}

	public function getJoomlaUserGroups()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__usergroups'))
			->order('title');
		$db->setQuery($query);
		return $db->loadObjectList('id');
	}
}

