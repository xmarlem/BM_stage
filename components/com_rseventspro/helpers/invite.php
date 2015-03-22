<?php
/**
* @package RSEvents!Pro
* @copyright (C) 2014 www.rsjoomla.com
* @license GPL, http://www.gnu.org/copyleft/gpl.html
*/
defined( '_JEXEC' ) or die( 'Restricted access' );

// Yahoo! Contacts
class RSYahoo {
	
	public static $emails = array();
	
	public static function auth($callback) {
		require_once JPATH_SITE.'/components/com_rseventspro/helpers/yahoo/yahoo.php';
		
		$config	= rseventsproHelper::getConfig();
		$key	= $config->yahoo_key;
		$secret = $config->yahoo_secret;
		$appid	= $config->yahoo_appid;
		
		// No credentials for the Yahoo! integration
		if (empty($key) && empty($secret) && empty($appid)) {
			return;
		}
		
		$hasSession = YahooSession::hasSession($key, $secret, $appid);
		
		if ($hasSession == FALSE) {
			$auth_url = YahooSession::createAuthorizationUrl($key, $secret, $callback);
		} else {
			$session = YahooSession::requireSession($key, $secret, $appid);
			
			if ($session) {
				$user = $session->getSessionedUser();
				$contacts = $user->getContacts(0,9999999);
				$contacts = @$contacts->contacts->contact;
				
				if (isset($contacts) && !empty($contacts)) {
					foreach ($contacts as $contact) {
						foreach ($contact->fields as $field) {
							if ($field->type == 'email') {
								self::$emails[] = $field->value;
							}
						}
					}
				}
			
				YahooSession::clearSession();
				$auth_url = YahooSession::createAuthorizationUrl($key, $secret, $callback);
			}
		}
		
		return $auth_url;
	}
	
	public static function getContacts() {
		if (!empty(self::$emails)) {
			return implode("\n", self::$emails);
		}
		
		return;
	}
}

// gMail Contacts

class RSGoogle
{
	/*
	*	Get the contact list of the current email address
	*
	*	Returns :
	*			0 - if the email or the password is empty
	*			1 - if there was an error connecting to the Google server
	*			2 - if there is no Authorization code
	*			3 - no contacts
	*			list of available contacts
	*
	*/
	
	public function getContacts($username,$password) {
		if (empty($username) || empty($password)) 
			return 0;
		
		$returns = array();
		$contacts = array();
		
		$username = stristr($username,'@') ? $username : $username.'@gmail.com';
		$login_url = "https://www.google.com/accounts/ClientLogin";
		$useragent = RSGoogle::useragents();
		
		$fields = array(
			'Email'       => $username,
			'Passwd'      => $password,
			'service'     => 'cp', // cp = Contact List
			'source'      => 'rsevents-google-contact-grabber',
			'accountType' => 'HOSTED_OR_GOOGLE',
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,$login_url);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS,$fields);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);
		$result = curl_exec($curl);

		if (empty($result)) return 1;
		
		$lines = explode("\n",$result);
		
		if (!empty($lines))
			foreach ($lines as $line) {
				$line = trim($line);
				if(!$line) continue;
				list($k,$v) = explode('=',$line,2);
				$returns[$k] = $v;
			}
		curl_close($curl);

		$feed_url = "https://www.google.com/m8/feeds/contacts/$username/full?alt=json&max-results=250";
		if (empty($returns['Auth'])) return 2;
		$header = array( 'Authorization: GoogleLogin auth=' . $returns['Auth'] );

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $feed_url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($curl, CURLOPT_USERAGENT, $useragent);

		$result = curl_exec($curl);
		curl_close($curl);
		$data = json_decode($result);
		if (empty($data)) return 3;
		
		foreach ($data->feed->entry as $entry) {			
			$name = $entry->title->{'$t'};
			$email = isset($entry->{'gd$email'}) ? $entry->{'gd$email'}[0]->address : '';
			$contacts[$name] = $email;
		}
		
		return $contacts;
	}
	
	/*
	*	Return the list of contacts
	*
	*/
	
	public function results($username,$password) {
		$result = '';
		$contacts = RSGoogle::getContacts($username,$password);
		
		if (empty($contacts) || $contacts == 0 || $contacts == 1 || $contacts == 2 || $contacts == 3) 
			return;
		
		foreach ($contacts as $name => $email) {
			$email = trim($email);
			if (!empty($email))
				$result .= $email."\n";
		}
		
		return rtrim($result,"\n");
	}
	
	public function useragents() {
		$useragents = array('Mozilla/5.0 (Windows NT 6.1; rv:13.0) Gecko/20100101 Firefox/13.0.1',
							'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:13.0) Gecko/20100101 Firefox/13.0.1',
							'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
							'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
							'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)',
							'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)'
							);
		
		$count = count($useragents) - 1;
		return $useragents[rand(0,$count)];
	}
}