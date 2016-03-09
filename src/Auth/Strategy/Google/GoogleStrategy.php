<?php
/**
 * Google strategy for Opauth
 * based on https://developers.google.com/accounts/docs/OAuth2
 * 
 * More information on Opauth: http://opauth.org
 * 
 * @copyright    Copyright © 2012 U-Zyn Chua (http://uzyn.com)
 * @link         http://opauth.org
 * @package      Opauth.GoogleStrategy
 * @license      MIT License
 */

/**
 * Google strategy for Opauth
 * based on https://developers.google.com/accounts/docs/OAuth2
 * 
 * @package			Opauth.Google
 */
class GoogleStrategy extends OpauthStrategy{
	
	/**
	 * Compulsory config keys, listed as unassociative arrays
	 */
	public $expects = array('client_id', 'client_secret');
	
	/**
	 * Optional config keys, without predefining any default values.
	 */
	public $optionals = array('redirect_uri', 'scope', 'state', 'access_type', 'approval_prompt');
	
	/**
	 * Optional config keys with respective default values, listed as associative arrays
	 * eg. array('scope' => 'email');
	 */
	public $defaults;
	
	public function __construct($strategy, $env)
	{
		$this->defaults = require dirname(__FILE__).'/defaults.config.php';
		parent::__construct($strategy, $env);
	}
	
	/**
	 * Auth request
	 */
	public function request(){
		$url = 'https://accounts.google.com/o/oauth2/auth';
		$params = array(
			'client_id' => $this->strategy['client_id'],
			'redirect_uri' => $this->strategy['redirect_uri'],
			'response_type' => 'code',
			'scope' => $this->strategy['scope']
		);

		foreach ($this->optionals as $key){
			if (!empty($this->strategy[$key])) $params[$key] = $this->strategy[$key];
		}
		
		$this->clientGet($url, $params);
	}
	
	/**
	 * Internal callback, after OAuth
	 */
	public function oauth2callback(){
		if (array_key_exists('code', $_GET) && !empty($_GET['code'])){
			$code = $_GET['code'];
			$url = 'https://accounts.google.com/o/oauth2/token';
			$params = array(
				'code' => $code,
				'client_id' => $this->strategy['client_id'],
				'client_secret' => $this->strategy['client_secret'],
				'redirect_uri' => $this->strategy['redirect_uri'],
				'grant_type' => 'authorization_code'
			);
			$response = $this->serverPost($url, $params, null, $headers);
			
			$results = json_decode($response);
			
			if (!empty($results) && !empty($results->access_token)){
				$userinfo = $this->userinfo($results->access_token);
				
				$this->auth = array(
					'uid' => $userinfo['id'],
					'info' => array(),
					'credentials' => array(
						'token' => $results->access_token,
						'expires' => date('c', time() + $results->expires_in)
					),
					'raw' => $userinfo
				);

				if (!empty($results->refresh_token))
				{
					$this->auth['credentials']['refresh_token'] = $results->refresh_token;
				}
				
				$this->mapProfile($userinfo, 'name', 'info.name');
				$this->mapProfile($userinfo, 'email', 'info.email');
				$this->mapProfile($userinfo, 'given_name', 'info.first_name');
				$this->mapProfile($userinfo, 'family_name', 'info.last_name');
				$this->mapProfile($userinfo, 'picture', 'info.image');
				
				$this->callback();
			}
			else{
				$error = array(
					'code' => 'access_token_error',
					'message' => 'Failed when attempting to obtain access token',
					'raw' => array(
						'response' => $response,
						'headers' => $headers
					)
				);

				$this->errorCallback($error);
			}
		}
		else{
			$error = array(
				'code' => 'oauth2callback_error',
				'raw' => $_GET
			);
			
			$this->errorCallback($error);
		}
	}
	
	/**
	 * Queries Google API for user info
	 *
	 * @param string $access_token 
	 * @return array Parsed JSON results
	 */
	private function userinfo($access_token){
		$userinfo = $this->serverGet('https://www.googleapis.com/oauth2/v1/userinfo', array('access_token' => $access_token), null, $headers);
		if (!empty($userinfo)){
			return $this->recursiveGetObjectVars(json_decode($userinfo));
		}
		else{
			$error = array(
				'code' => 'userinfo_error',
				'message' => 'Failed when attempting to query for user information',
				'raw' => array(
					'response' => $userinfo,
					'headers' => $headers
				)
			);

			$this->errorCallback($error);
		}
	}
	
	/**
	 * Basic server-side HTTP POST request via self::httpRequest(), wrapper of file_get_contents
	 * 
	 * @param string $url Destination URL
	 * @param array $data Data to be POSTed
	 * @param array $options Additional stream context options, if any
	 * @param string $responseHeaders Response headers after HTTP call. Useful for error debugging.
	 * @return string Content resulted from request, without headers
	 */
	public static function serverPost($url, $data, $options = array(), &$responseHeaders = null) 
	{
		if (!is_array($options)) {
			$options = array();
		}

		$query = http_build_query($data, '', '&');

		$stream = array('http' => array(
			'method' 	=> 'POST',
			'header' 	=> "Content-type: application/x-www-form-urlencoded",
			'content' 	=> $query
		));

		$stream = array_merge_recursive($options, $stream);
		
		return self::httpRequest($url, $stream, $responseHeaders);	
	}
	
	/**
	 * Basic server-side HTTP GET request via self::httpRequest(), wrapper of file_get_contents
	 * 
	 * @param string $url Destination URL
	 * @param array $data Data to be submitted via GET
	 * @param array $options Additional stream context options, if any
	 * @param string $responseHeaders Response headers after HTTP call. Useful for error debugging.
	 * @return string Content resulted from request, without headers
	 */
	public static function serverGet($url, $data, $options = null, &$responseHeaders = null) {
		return self::httpRequest($url.'?'.http_build_query($data, '', '&'), $options, $responseHeaders);
	}
	
	
    public static function httpRequest($url, $options = null, &$responseHeaders = null) 
    {

        if (!function_exists('curl_init')) {

            trigger_error('Curl not supported, try using other http_client_method such as file', E_USER_ERROR);
        }

        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 7);
        
        $curlOpt = [
			CURLOPT_SSL_VERIFYPEER	=> false,
			CURLOPT_SSL_VERIFYHOST	=> false,
			CURLOPT_SSLVERSION		=> 1, // Force TLS version 1
			CURLINFO_HEADER_OUT 	=> true,
			CURLOPT_HTTPHEADER		=> ['User-Agent: opauth','Expect:','Content-type: application/x-www-form-urlencoded'],
			// CURLOPT_FAILONERROR		=> true,
			CURLOPT_TIMEOUT			=> 300,
			CURLOPT_CONNECTTIMEOUT	=> 7,
			CURLOPT_ENCODING		=> '',
		];
		
		foreach($curlOpt as $optionType => $optionValue) {
			curl_setopt($ch, $optionType, $optionValue);
		}
        

        if (!empty($options['http']['method']) && strtoupper($options['http']['method']) === 'POST') {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['http']['content']);

        }
		
        $content = curl_exec($ch);
        
        curl_close($ch);

        list($headers, $content) = explode("\r\n\r\n", $content, 2);

        $responseHeaders = $headers;

        return $content;

    }
	
}