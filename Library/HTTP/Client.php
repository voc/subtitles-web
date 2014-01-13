<?php
	
	requires(
		'HTTP/Client/Response'
	);
	
	class HTTP_Client {
		
		protected $_curl;
		
		protected $_cookies;
		protected $_options;
		
		const KEEP_REFERER = 4096;
		const KEEP_COOKIES = 4097;
		
		public function __construct(array $options = array()) {
			$this->_curl = curl_init();
			$this->_cookies = array();
			$this->_options = array(
				self::KEEP_REFERER => true,
				self::KEEP_COOKIES => true,
			);
			
			curl_setopt_array($this->_curl, array(
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_FOLLOWLOCATION => false, // TODO: as config option
				CURLOPT_HEADER => true
			));
			
			if (!empty($options)) {
				foreach($options as $option => $value) {
					$this->setOption($option, $value);
				}
			}
		}
		
		public function __destruct() {
			curl_close($this->_curl);
		}
		
		public function setOption($option, $value) {
			switch($option) {
				case self::KEEP_REFERER:
				case self::KEEP_COOKIES:
					$this->_options[$option] = $value;
					break;
				default:
					curl_setopt($this->_curl, $option, $value);
					break;
			}
		}
		
		// TODO: store in _options
		public function setTimeout($timeout) {
			curl_setopt($this->_curl, CURLOPT_TIMEOUT, $timeout);
		}
		
		public function setProxy($proxy, $user = null, $password = '') {
			curl_setopt($this->_curl, CURLOPT_PROXY, $proxy);
			
			if ($user !== null) {
				curl_setopt($this->_curl, CURLOPT_PROXYUSERPWD, $username . ':' . $password);
			}
		}
		
		public function getCookie($cookie) {
			if (!isset($this->_cookies[$cookie])) {
				return false;
			}
			
			return $this->_cookies[$cookie];
		}
		
		public function setCookie($cookie, $value) {
			if (empty($cookie)) {
				return false;
			}
			
			$this->_cookies[$cookie] = $value;
			
			return true;
		}
		
		public function removeCooke($cookie) {
			if (!isset($this->_cookies[$cookies])) {
				return false;
			}
			
			unset($this->_cookies[$cookies]);
			
			return false;
		}
		
		public function setReferer($referer) {
			curl_setopt($this->_curl, CURLOPT_REFERER, $referer);
		}
		
		public function setUserAgent($agent) {
			curl_setopt($this->_curl, CURLOPT_USERAGENT, $agent);
		}
		
		public function setAuthentication($user, $password = '', $safe = false) {
			curl_setopt($this->_curl, CURLOPT_HTTPAUTH, ($safe)? CURLAUTH_ANYSAFE : CURLAUTH_ANY); 
			curl_setopt($this->_curl, CURLOPT_USERPWD, $user . ':' . $password); 
		}
		
		public function head($url, $port = null) {
			return $this->_request($url, $port, array(CURLOPT_NOBODY => true));
		}
		
		public function get($url, $port = null) {
			return $this->_request($url, $port);
		}
		
		// TODO: move port to the end?
		public function post($url, array $data = array(), $port = null, $formData = false) {
			return $this->_request($url, $port, array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => ($formData)? $data : http_build_query($data)
			));
		}
		
		// TODO: maybe add postFile/postMultipart?
		
		public function clearCookies() {
			$this->_cookies = array();
		}
		
		protected function _request($url, $port = null, array $options = array()) {
			$curl = curl_copy_handle($this->_curl);
			
			curl_setopt($curl, CURLOPT_URL, $url);
			
			if (!empty($this->_cookies)) {
				$cookies = '';
				
				foreach($this->_cookies as $cookie => $value) {
					$cookies .= $cookie . '=' . $value . ';';
				}
				
				curl_setopt($curl, CURLOPT_COOKIE, substr($cookies, 0, -1));
			}
			
			if ($port !== null) {
				curl_setopt($curl, CURLOPT_PORT, $port);
			}
			
			if (!empty($options)) {
				curl_setopt_array($curl, $options);
			}
			
			$content = curl_exec($curl);
			$info = curl_getinfo($curl);
			
			$response = new HTTP_Client_Response(
				$info['http_code'],
				self::_parseHeader(substr($content, 0, $info['header_size'])),
				substr($content, $info['header_size'])
			);
			
			curl_close($curl);
			
			if ($this->_options[self::KEEP_REFERER] !== false) {
				curl_setopt($this->_curl, CURLOPT_REFERER, $url);
			}
			
			return $response;
		}
		
		protected static function _parseHeader($content) {
			$header = array();
			
			foreach (explode("\r\n", $content) as $line) {
				$parts = explode(':', $line, 2);
				
				if (count($parts) != 2) {
					continue;
				}
				
				$header[trim($parts[0])] = trim($parts[1]);
			}
			
			return $header;
		}
		
	}
	
?>