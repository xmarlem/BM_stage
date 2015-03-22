<?php
/**
* @version 1.0.0
* @package RSEvents!Pro 1.0.0
* @copyright (C) 2007-2012 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.plugin.plugin' );

class plgSystemRsepropaypal extends JPlugin
{
	//set the value of the payment option
	var $rsprooption = 'paypal';
	
	public function __construct( &$subject, $config ) {
		parent::__construct( $subject, $config );
	}
	
	public function onAfterInitialise() {		
		$app	= JFactory::getApplication();
		$jinput	= $app->input;
		
		if($app->getName() != 'site') 
			return;

		$paypal = $jinput->getInt('paypalrsepro');
		if (!empty($paypal))
			$this->rsepro_processForm(array());
	}
	
	/*
	*	Is RSEvents!Pro installed
	*/
	
	protected function canRun() {
		$helper = JPATH_SITE.'/components/com_rseventspro/helpers/rseventspro.php';
		if (file_exists($helper)) {
			require_once $helper;
			JFactory::getLanguage()->load('plg_system_rsepropaypal',JPATH_ADMINISTRATOR);
			
			return true;
		}
		
		return false;
	}
	
	/*
	*	Add the current payment option to the Payments List
	*/

	public function rsepro_addOptions() {
		if ($this->canRun())
			return JHTML::_('select.option', $this->rsprooption, JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_NAME'));
		else return JHTML::_('select.option', '', '');
	}
	
	/*
	*	Add optional fields for the payment plugin. Example: Credit Card Number, etc.
	*	Please use the syntax <form method="post" action="index.php?option=com_rseventspro&task=process" name="paymentForm">
	*	The action provided in the form will actually run the rsepro_processForm() of your payment plugin.
	*/
	
	public function rsepro_showForm($vars) {
		$app =& JFactory::getApplication();		
		if($app->getName() != 'site') return;
		
		//check to see if we can show something
		if (!$this->canRun()) return;
		
		if (isset($vars['method']) && $vars['method'] == $this->rsprooption) {
			JFactory::getLanguage()->load('com_rseventspro',JPATH_SITE);
		
			jimport('joomla.mail.helper');
			$db		= JFactory::getDbo();
			$query	= $db->getQuery(true);
			
			//is the plugin enabled ?
			$enable = JPluginHelper::isEnabled('system', 'rsepropaypal');
			if (!$enable) return;
			
			$details = $vars['details'];
			$tickets = $vars['tickets'];
			
			//check to see if its a payment request
			if (empty($details->verification) && empty($details->ide) && empty($details->email) && empty($tickets)) 
				return;
			
			//get the currency
			$currency = $vars['currency'];
			
			//get paypal details
			$paypal_email		= $this->params->get('paypal_email','');
			$paypal_return		= $this->params->get('return_url','');
			$paypal_lang		= $this->params->get('paypal_lang','US');
			
			$query->clear()
				->select($db->qn('name'))
				->from($db->qn('#__rseventspro_events'))
				->where($db->qn('id').' = '.(int) $details->ide);
			
			$db->setQuery($query);
			$event = $db->loadObject();
			
			//do we allow users to sell their own tickets?
			if (rseventsproHelper::getConfig('payment_paypal','int')) {
				$query->clear()
					->select($db->qn('paypal_email'))
					->from($db->qn('#__rseventspro_events'))
					->where($db->qn('id').' = '.(int) $details->ide);
				
				$db->setQuery($query);
				$user_paypal = $db->loadResult();
				
				if (!empty($user_paypal)) 
					$paypal_email = $user_paypal;
			}
			
			//check if the e-mail address is valid
			if (!JMailHelper::isEmailAddress($paypal_email)) {
				echo JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_NO_VALID_EMAIL');
				return;
			}
			
			//check to see if the return_url is valid
			if (substr($paypal_return,0,4) != 'http') 
				$paypal_return = '';
			
			//get the url for paypal
			$paypal_url = $this->params->get('paypal_mode') ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';
			
			if (count($tickets) == 1) {
				$ticket			= $tickets[0];
				$paypal_item	= htmlentities($event->name.' - '.$ticket->name,ENT_QUOTES,'UTF-8');
				$paypal_total	= isset($ticket->price) ? $ticket->price : 0;
				$paypal_number	= isset($ticket->quantity) ? $ticket->quantity : 1;
			} else {
				$paypal_item	= htmlentities($event->name.' - '.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_MULTIPLE'),ENT_QUOTES,'UTF-8');
				$paypal_total	= 0;
				$paypal_number	= 1;
				
				foreach ($tickets as $ticket) {
					if ($ticket->price > 0)
						$paypal_total += ($ticket->price * $ticket->quantity);
				}
			}
			
			if ($paypal_total == 0) return;
			
			$thetax = 0;
			$thediscount = 0;
			
			if ($details->early_fee)
				$thediscount += $details->early_fee;
			
			if ($details->late_fee)
				$thetax += $details->late_fee;
			
			$html = '';		
			$html .= '<fieldset>'."\n";
			$html .= '<legend>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_TICKETS_INFO').'</legend>'."\n";
			$html .= '<table cellspacing="10" cellpadding="0" class="table table-bordered rs_table">'."\n";
			$html .= '<tr>'."\n";
			$html .= '<td>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_TICKETS').'</td>'."\n";
			$html .= '<td>'."\n";
			
			$discount = $details->discount;
			$total = 0;
			if (!empty($tickets)) { 
				foreach ($tickets as $ticket) {
					if (empty($ticket->price))
						$html .= $ticket->quantity. ' x '.$ticket->name.' ('.JText::_('COM_RSEVENTSPRO_GLOBAL_FREE'). ')<br />';
					else
						$html .= $ticket->quantity. ' x '.$ticket->name.' ('.rseventsproHelper::currency($ticket->price). ')<br />';
					
					if ($ticket->price > 0)
						$total += ($ticket->quantity * $ticket->price);
				}
			} 
			if (!empty($discount)) $total = $total - $discount;
			
			$html .= '</td>'."\n";
			$html .= '</tr>'."\n";
			
			if (!empty($discount)) {
				$html .= '<tr>'."\n";
				$html .= '<td>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_TICKETS_DISCOUNT').'</td>'."\n";
				$html .= '<td>'.rseventsproHelper::currency($discount).'</td>'."\n";
				$html .= '</tr>'."\n";
			}
			
			if ($details->early_fee) {
				$total = $total - $details->early_fee;
				$html .= '<tr>'."\n";
				$html .= '<td>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_EARLY_FEE').'</td>'."\n";
				$html .= '<td>'."\n";
				$html .= rseventsproHelper::currency($details->early_fee);
				$html .= '</td>'."\n";
				$html .= '</tr>'."\n";
			}
			
			if ($details->late_fee) {
				$total = $total + $details->late_fee;
				$html .= '<tr>'."\n";
				$html .= '<td>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_LATE_FEE').'</td>'."\n";
				$html .= '<td>'."\n";
				$html .= rseventsproHelper::currency($details->late_fee);
				$html .= '</td>'."\n";
				$html .= '</tr>'."\n";
			}
			
			if (!empty($details->tax)) {
				$total = $total + $details->tax;
				$html .= '<tr>'."\n";
				$html .= '<td>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_TICKETS_TAX').'</td>'."\n";
				$html .= '<td>'.rseventsproHelper::currency($details->tax).'</td>'."\n";
				$html .= '</tr>'."\n";
			}
			
			$html .= '<tr>'."\n";
			$html .= '<td>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_TICKETS_TOTAL').'</td>'."\n";
			$html .= '<td>'.rseventsproHelper::currency($total).'</td>'."\n";
			$html .= '</tr>'."\n";
			
			$html .= '</table>'."\n";
			$html .= '</fieldset>'."\n";
			
			$html .= '<p style="margin: 10px;font-weight: bold;">'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_REDIRECTING').'</p>'."\n";
			
			$html .= '<form method="post" action="'.$paypal_url.'" id="paypalForm">'."\n";
			$html .= '<input type="hidden" name="business" value="'.$this->escape($paypal_email).'" />'."\n";
			$html .= '<input type="hidden" name="item_name" value="'.$paypal_item.'" />'."\n";
			$html .= '<input type="hidden" name="currency_code" value="'.$this->escape($currency).'" />'."\n";
			
			$html .= '<input type="hidden" name="cmd" value="_xclick" />'."\n";
			$html .= '<input type="hidden" name="bn" value="RSJoomla_SP" />'."\n";
			$html .= '<input type="hidden" name="charset" value="utf-8" />'."\n";
			$html .= '<input type="hidden" name="amount" value="'.$this->convertprice($paypal_total).'" />'."\n";
			$html .= '<input type="hidden" name="notify_url" value="'.JRoute::_(JURI::root().'index.php?paypalrsepro=1').'" />'."\n";
			
			$html .= '<input type="hidden" name="custom" value="'.$this->escape($details->verification).'" />'."\n";
			if (!empty($paypal_return))
				$html .= '<input type="hidden" name="return" value="'.$this->escape($paypal_return).'" />'."\n";
			
			
			if (!empty($details->tax))
				$thetax += $details->tax;
			
			if (!empty($thetax))
				$html .= '<input type="hidden" name="tax" value="'.$this->convertprice($thetax).'" />'."\n";
			
			if (!empty($details->discount))
				$thediscount += $details->discount;
			
			if (!empty($thediscount))
				$html .= '<input type="hidden" name="discount_amount" value="'.$this->convertprice($thediscount).'" />'."\n";
			$html .= '<input type="hidden" name="quantity" value="'.intval($paypal_number).'" />'."\n";
			$html .= '<input type="hidden" name="lc" value="'.$this->escape($paypal_lang).'" />'."\n";
			$html .= '</form>'."\n";
			
			
			$html .= '<script type="text/javascript">'."\n";
			$html .= 'function paypalFormSubmit() { document.getElementById(\'paypalForm\').submit() }'."\n";
			$html .= 'try { window.addEventListener ? window.addEventListener("load",paypalFormSubmit,false) : window.attachEvent("onload",paypalFormSubmit); }'."\n";
			$html .= 'catch (err) { paypalFormSubmit(); }'."\n";
			$html .= '</script>'."\n";
			
			echo $html;			
		}
		
	}
	
	/*
	*	Process the form
	*/
	
	public function rsepro_processForm($vars) {
		//check to see if we can show something
		if (!$this->canRun()) 
			return;
		
		$db		= JFactory::getDbo();
		$app	= JFactory::getApplication();
		$jinput	= $app->input;
		$query	= $db->getQuery(true);
		$log	= array();
		$params = array();
		
		// read the post from PayPal system
		$post = $_POST;

		// assign posted variables to local variables
		$custom = $jinput->getString('custom');
		if (empty($custom)) return;
		
		$query->clear()
			->select($db->qn('id'))
			->from($db->qn('#__rseventspro_users'))
			->where($db->qn('verification').' = '.$db->q($custom));
		
		$db->setQuery($query);
		$subscriber = $db->loadResult();
		
		$query->clear()
			->select($db->qn('state'))
			->from($db->qn('#__rseventspro_users'))
			->where($db->qn('id').' = '.(int) $subscriber);
		
		$db->setQuery($query);
		$state = $db->loadResult();
		
		if ($state == 1) 
			return;
		
		$query->clear()
			->select($db->qn('gateway'))
			->from($db->qn('#__rseventspro_users'))
			->where($db->qn('id').' = '.(int) $subscriber);
		
		$db->setQuery($query);
		$gateway = $db->loadResult();
		
		if ($gateway != $this->rsprooption)
			return;
		
		$item_name			= $_POST['item_name'];
		$item_number		= $_POST['item_number'];
		$payment_status		= $_POST['payment_status'];
		$payment_amount		= $_POST['mc_gross'];
		$payment_currency	= $_POST['mc_currency'];
		$receiver_email		= $_POST['receiver_email'];
		$payer_email		= $_POST['payer_email'];
		
		// post back to PayPal system to validate
		$url = $this->params->get('paypal_mode') ? 'https://www.paypal.com/cgi-bin/webscr' : 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		$req = $this->_buildPostData();
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: www.paypal.com'));
		$res = curl_exec($ch);
		$errstr = curl_error($ch);
		curl_close($ch);
		
		$log[] = "Receiving a new transaction from Paypal.";
		
		if ($this->params->get('paypal_mode',0) == 0)
			$log[] = "Demo mode is on.";
		
		if ($res) {
			if (strcmp ($res, "VERIFIED") == 0) {
				$log[] = "PayPal reported a valid transaction.";
				$log[] = "Payment status is ".(!empty($payment_status) ? $payment_status : 'empty').".";
				
				// check the payment_status is Completed
				if ($payment_status == 'Completed' || ($this->params->get('paypal_mode') == 0)) {
					//set the transaction params
					if(!empty($post))
						foreach($post as $key=>$value)
							$params[] = $db->escape($key.'='.$value);
					
					$params = is_array($params) ? implode("\n",$params) : '';
					
					if(!empty($subscriber)) {
						$query->clear()
							->select($db->qn('t.price'))->select($db->qn('ut.quantity'))
							->from($db->qn('#__rseventspro_user_tickets','ut'))
							->join('left', $db->qn('#__rseventspro_tickets','t').' ON '.$db->qn('t.id').' = '.$db->qn('ut.idt'))
							->where($db->qn('ut.ids').' = '.(int) $subscriber);
						
						$db->setQuery($query);
						$tickets = $db->loadObjectList();
						
						$total_price = 0;
						if (!empty($tickets)) {
							foreach ($tickets as $ticket) {				
								if ($ticket->price > 0)
									$total_price += ($ticket->quantity * $ticket->price);
							}
						}
						
						$query->clear()
							->select($db->qn('discount'))->select($db->qn('early_fee'))
							->select($db->qn('late_fee'))->select($db->qn('tax'))
							->from($db->qn('#__rseventspro_users'))
							->where($db->qn('id').' = '.(int) $subscriber);
						
						$db->setQuery($query);
						$details = $db->loadObject();
						
						// check if the amount is correct
						if (!empty($details->discount)) 
							$total = $total_price - $details->discount; 
						else $total = $total_price;
						
						if (!empty($details->early_fee))
							$total = $total - $details->early_fee;
						
						if (!empty($details->late_fee))
							$total = $total + $details->late_fee;
						
						//add tax
						if (!empty($details->tax))
							$total = $total + $details->tax;							
						
						if (number_format($total,2) == $payment_amount) {
							//set the subscription state to Accepted
							$query->clear()
								->update($db->qn('#__rseventspro_users'))
								->set($db->qn('state').' = 1')
								->set($db->qn('params').' = '.$db->q($params))
								->where($db->qn('id').' = '.(int) $subscriber);
							
							$db->setQuery($query);
							$db->execute();
							
							$log[] = "Successfully added the payment to the database.";
							
							//send the activation email
							require_once JPATH_SITE.'/components/com_rseventspro/helpers/emails.php';
							
							rseventsproHelper::confirm($subscriber);
							rseventsproHelper::savelog($log,$subscriber);
						} else {
							$currency = rseventsproHelper::getConfig('payment_currency');
							$log[] = "Expected an amount of $total $currency. PayPal reports this payment is $payment_amount $currency. Stopping.";
							rseventsproHelper::savelog($log,$subscriber);
						}
					}
				}
				else {
					// log for manual investigation
					$log[] = 'Paypal reported a transaction with the status of : '.$payment_status;
					
					rseventsproHelper::savelog($log,$subscriber);
					return;
				}
			} elseif (strcmp($res, "INVALID") == 0) {
				// log for manual investigation
				$log[] = "Could not verify transaction authencity. PayPal said it's invalid.";
				$log[] = "String sent to PayPal is $req";
				rseventsproHelper::savelog($log,$subscriber);
			}
		} else {
			$log[] = "Could not connect to $url in order to verify this transaction. Error reported is: $errstr";
			rseventsproHelper::savelog($log,$subscriber);
		}
	}
	
	public function rsepro_tax($vars) {
		if (!$this->canRun()) 
			return;
		
		if (isset($vars['method']) && $vars['method'] == $this->rsprooption) {
			$total		= isset($vars['total']) ? $vars['total'] : 0;
			$tax_value	= $this->params->get('tax_value',0);
			$tax_type	= $this->params->get('tax_type',0);
			
			return rseventsproHelper::setTax($total,$tax_type,$tax_value);
		}
	}
	
	public function rsepro_info($vars) {
		if (!$this->canRun()) 
			return;
		
		if (isset($vars['method']) && $vars['method'] == $this->rsprooption) {
			$app = JFactory::getApplication();
			
			$params	= array();
			$data	= $vars['data'];
			
			if (!empty($data)) {
				if (!is_array($data)) {
					$data	= explode("\n",$data);
					if (!empty($data)) {
						foreach ($data as $line) {
							$linearray = explode('=',$line);
							
							if (!empty($linearray))
								$params[trim($linearray[0])] = trim($linearray[1]);
						}
					}
				} else {
					$params = $data;
				}
				
				echo $app->isAdmin() ? '<fieldset>' : '<fieldset class="rs_fieldset">';
				echo '<legend>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_PAYMENT_DETAILS').'</legend>';
				echo '<table width="100%" border="0" class="table table-striped adminform rs_table">';
				echo '<tr>';
				echo '<td width="25%" align="right"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_TRANSACTION_ID').'</b></td>';
				echo '<td>'.$params['txn_id'].'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="25%" align="right"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_PAYER_NAME').'</b></td>';
				echo '<td>'.$params['first_name'].' '.$params['last_name'].'</td>';
				echo '</tr>';
				echo '<tr>';
				echo '<td width="25%" align="right"><b>'.JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_PAYER_EMAIL').'</b></td>';
				echo '<td>'.$params['payer_email'].'</td>';
				echo '</tr>';
				echo '</table>';
				echo '</fieldset>';
			}
		}
	}
	
	public function rsepro_name($vars) {
		if (!$this->canRun()) 
			return;
		
		if (isset($vars['gateway']) && $vars['gateway'] == $this->rsprooption) {
			return JText::_('COM_RSEVENTSPRO_PLG_PLUGIN_PAYPAL_NAME');
		}
	}
	
	protected function _buildPostData() {
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
			
		//reading raw POST data from input stream. reading pot data from $_POST may cause serialization issues since POST data may contain arrays
		$raw_post_data = file_get_contents('php://input');
		if ($raw_post_data) {
			$raw_post_array = explode('&', $raw_post_data);
			$myPost = array();
			foreach ($raw_post_array as $keyval) {
				$keyval = explode ('=', $keyval);
				if (count($keyval) == 2) {
					$myPost[$keyval[0]] = urldecode($keyval[1]);
				}
			}
			
			$get_magic_quotes_exists 	= function_exists('get_magic_quotes_gpc');
			$get_magic_quotes_gpc 		= get_magic_quotes_gpc();
			
			foreach ($myPost as $key => $value) {
				if ($key == 'limit' || $key == 'limitstart' || $key == 'option') continue;
				
				if ($get_magic_quotes_exists && $get_magic_quotes_gpc) {
					$value = urlencode(stripslashes($value)); 
				} else {
					$value = urlencode($value);
				}
				$req .= "&$key=$value";
			}
		} else {
			// read the post from PayPal system
			$post = $_POST;
			foreach ($post as $key => $value) {
				if ($key == 'limit' || $key == 'limitstart' || $key == 'option') continue;
				
				$value = urlencode($value);
				$req .= "&$key=$value";
			}
		}
		
		return $req;
	}
	
	protected function escape($string) {
		return htmlentities($string, ENT_COMPAT, 'UTF-8');
	}
	
	protected function convertprice($price) {
		return number_format($price, 2, '.', '');
	}
}