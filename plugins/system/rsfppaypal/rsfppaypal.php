<?php
/**
* @package RSMembership!
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/licenses/gpl-2.0.html
*/

defined('_JEXEC') or die('Restricted access');

class plgSystemRSFPPayPal extends JPlugin
{
	protected $componentId 	= 500;
	protected $componentValue = 'paypal';
	
	public function __construct( &$subject, $config )
	{
		parent::__construct( $subject, $config );
		$this->newComponents = array(500);
	}
	
	public function rsfp_bk_onAfterShowComponents()
	{
		$lang = JFactory::getLanguage();
		$lang->load( 'plg_system_rsfppaypal' );
		
		$mainframe 	= JFactory::getApplication();
		$db 		= JFactory::getDBO();
		$formId 	= JRequest::getInt('formId');
		
		$link = "displayTemplate('".$this->componentId."')";
		if ($components = RSFormProHelper::componentExists($formId, $this->componentId))
			$link = "displayTemplate('".$this->componentId."', '".$components[0]."')";
		?>
		<li><a href="javascript: void(0);" onclick="<?php echo $link;?>;return false;" id="rsfpc<?php echo $this->componentId; ?>"><span id="paypal"><?php echo JText::_('RSFP_PAYPAL_COMPONENT'); ?></span></a></li>
		<?php
	}
	
	public function rsfp_getPayment(&$items, $formId)
	{
		if ($components = RSFormProHelper::componentExists($formId, $this->componentId))
		{
			$data = RSFormProHelper::getComponentProperties($components[0]);
			
			$item 			= new stdClass();
			$item->value 	= $this->componentValue;
			$item->text 	= $data['LABEL'];
			
			// add to array
			$items[] = $item;
		}
	}
	
	public function rsfp_doPayment($payValue, $formId, $SubmissionId, $price, $products, $code)
	{
		// execute only for our plugin
		if ($payValue != $this->componentValue) return;
		
		if ($price > 0) {		
			list($replace, $with) = RSFormProHelper::getReplacements($SubmissionId);
			
			$args = array(
				'cmd' 			=> '_xclick',
				'business'		=> RSFormProHelper::getConfig('paypal.email'),
				'item_name'		=> implode(', ', $products),
				'currency_code'	=> RSFormProHelper::getConfig('payment.currency'),
				'amount'		=> number_format($price, 2, '.', ''),
				'notify_url'	=> JURI::root().'index.php?option=com_rsform&formId='.$formId.'&task=plugin&plugin_task=paypal.notify&code='.$code,
				'charset'		=> 'utf-8',
				'lc'			=> RSFormProHelper::getConfig('paypal.language') ? RSFormProHelper::getConfig('paypal.language') : 'US',
				'bn'			=> 'RSJoomla_SP'
			);
			
			// Add cancel URL
			if ($cancel = RSFormProHelper::getConfig('paypal.cancel')) {
				$args['cancel_return'] = str_replace($replace, $with, $cancel);
			}
			
			// Add return URL
			if ($return = RSFormProHelper::getConfig('paypal.return')) {
				$args['return'] = str_replace($replace, $with, $return);
			}
			
			// Add tax
			if ($tax = RSFormProHelper::getConfig('paypal.tax.value')) {
				if (RSFormProHelper::getConfig('paypal.tax.type')) {
					$args['tax'] = $tax;
				} else {
					$args['tax_rate'] = $tax;
				}
			}
			
			// Get a new instance of the PayPal object. This is used so that we can programatically change values sent to PayPal through the "Scripts" areas.
			$paypal = RSFormProPayPal::getInstance();
			
			// If any options have already been set, use this to override the ones used here
			$paypal->args = array_merge($args, $paypal->args);
			$paypal->url  = RSFormProHelper::getConfig('paypal.test') ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			
			JFactory::getApplication()->redirect($paypal->url.'?'.http_build_query($paypal->args, '', '&'));
		}
	}
	
	public function rsfp_bk_onAfterCreateComponentPreview($args = array())
	{
		if ($args['ComponentTypeName'] == 'paypal')
		{
			$args['out'] = '<td>&nbsp;</td>';
			$args['out'].= '<td><img src="'.JURI::root(true).'/administrator/components/com_rsform/assets/images/icons/paypal.png" /> '.$args['data']['LABEL'].'</td>';	
		}
	}
	
	public function rsfp_bk_onAfterShowConfigurationTabs($tabs)
	{
		$lang = JFactory::getLanguage();
		$lang->load( 'plg_system_rsfppaypal' );
		
		$tabs->addTitle(JText::_('RSFP_PAYPAL_LABEL'), 'form-paypal');
		$tabs->addContent($this->paypalConfigurationScreen());
	}
	
	public function rsfp_f_onSwitchTasks()
	{
		//Notification receipt from Paypal
		if (JRequest::getVar('plugin_task') == 'paypal.notify')
		{
			$db 	= JFactory::getDBO();
			$code 	= JRequest::getVar('code');
			$formId = JRequest::getInt('formId');
			$db->setQuery("SELECT SubmissionId FROM #__rsform_submissions s WHERE s.FormId='".$formId."' AND MD5(CONCAT(s.SubmissionId,s.DateSubmitted)) = '".$db->escape($code)."'");
			if ($SubmissionId = $db->loadResult())
			{
				$db->setQuery("UPDATE #__rsform_submission_values sv SET sv.FieldValue=1 WHERE sv.FieldName='_STATUS' AND sv.FormId='".$formId."' AND sv.SubmissionId = '".$SubmissionId."'");
				$db->execute();
				
				$mainframe = JFactory::getApplication();
				$mainframe->triggerEvent('rsfp_afterConfirmPayment', array($SubmissionId));
			}
			jexit('ok');
		}
	}
	
	public function paypalConfigurationScreen()
	{
		ob_start();
		
		?>
		<div id="page-paypal" class="com-rsform-css-fix">
			<table  class="admintable">
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="currency"><?php echo JText::_( 'RSFP_PAYPAL_EMAIL' ); ?></label></td>
					<td><input type="text" name="rsformConfig[paypal.email]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('paypal.email')); ?>" size="100" maxlength="64"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="return"><?php echo JText::_( 'RSFP_PAYPAL_RETURN' ); ?></label></td>
					<td><input type="text" name="rsformConfig[paypal.return]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('paypal.return'));  ?>" size="100"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="cancel"><?php echo JText::_( 'RSFP_PAYPAL_CANCEL' ); ?></label></td>
					<td><input type="text" name="rsformConfig[paypal.cancel]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('paypal.cancel'));  ?>" size="100"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="currency"><?php echo JText::_( 'RSFP_PAYPAL_TEST' ); ?></label></td>
					<td><?php echo JHTML::_('select.booleanlist', 'rsformConfig[paypal.test]' , '' , RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('paypal.test')));?></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="tax.type"><?php echo JText::_( 'RSFP_PAYPAL_TAX_TYPE' ); ?></label></td>
					<td><?php echo JHTML::_('select.booleanlist', 'rsformConfig[paypal.tax.type]' , '' , RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('paypal.tax.type')), JText::_('RSFP_PAYPAL_TAX_TYPE_FIXED'), JText::_('RSFP_PAYPAL_TAX_TYPE_PERCENT'));?></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="tax.value"><?php echo JText::_( 'RSFP_PAYPAL_TAX_VALUE' ); ?></label></td>
					<td><input type="text" name="rsformConfig[paypal.tax.value]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('paypal.tax.value'));  ?>" size="4" maxlength="5"></td>
				</tr>
				<tr>
					<td width="200" style="width: 200px;" align="right" class="key"><label for="language"><?php echo JText::_( 'RSFP_PAYPAL_LANGUAGE' ); ?></label></td>
					<td>
						<input type="text" name="rsformConfig[paypal.language]" value="<?php echo RSFormProHelper::htmlEscape(RSFormProHelper::getConfig('paypal.language'));  ?>" size="4" maxlength="2">
						<?php echo JText::_('PAYPAL_LANGUAGES_CODES') ?>
					</td>
				</tr>
			</table>
		</div>
		<?php
		
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}
}

class RSFormProPayPal
{
	public $args = array();
	public $url;
	
	public static function getInstance() {
		static $inst;
		if (!$inst) {
			$inst = new RSFormProPayPal;
		}
		
		return $inst;
	}
}