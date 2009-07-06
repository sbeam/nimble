<?php
	require_once(dirname(__FILE__) . '/base.php');
	/**
		* Nimbler Mailer is a wrapper for the php mail() function
		* Allowing html and text email templates to be bundled into one script
		* also allowing multiple emails to be sent
		* ----NOTE!!----
		* This script is not suitable yet for mass mailing in since the mail() 
		* function openes a new SMTP socket for each call of mail() this script 
		* will break down and become slow around 5-10 emails depending on server load
		* @todo queue support
		* @package Nimble
		*/
	class NimbleMailer {
		
		var $view_path = '';
		var $nimble = NULL;
		var $recipiants = array();
		var $from = '';
		var $time = '';
		var $headers = '';
		var $_divider = '';
		var $_content = '';
		
		public function __construct() {
			$this->nimble = Nimble::getInstance();
			$this->view_path = $nimble->config['view_path'] = '/Users/srdavis/Documents/temp_mail';
			$this->view_path = $nimble->config['view_path'];
			$this->class = get_called_class();
			$this->divider = '------=_' . (rand(100000000, 99999999999) * 2);
		}
		
		public function __call($method, $args) {
			self::__callStatic($method, $args);
		}
		
		
		
		public static function __callStatic($method, $args) {
			$matches = array();
			$class = get_called_class();
			$klass = new $class();
			if(preg_match('/^(deliver|create|queue)_(.+)$/', $method, $matches)) {
				switch($matches[1]) {
					case 'deliver':
						$klass->load_method($matches[2], $args);
						//php template
						$klass->prep_template(FileUtils::join($klass->view_path, strtolower($class), $matches[2] . '.php'), 'html');
						//text template
						$text_template = FileUtils::join($klass->view_path, strtolower($class), $matches[2] . '.txt');
						if(file_exists($text_template)) {
							$klass->prep_template($text_template, 'text');
						}
						$this->_content = array();
						$klass->send_mail();
					break;
					case 'create':
					
					break;
					case 'queue':
						//Not implimented yet
					break;
				}
			}
		}
		
		private function load_method($method, $args) {
			call_user_func(array($this, $method), $args);
			if(!is_array($this->recipiants) && is_string($this->recipiants)) {
				$this->recipiants = array($this->recipiants);
			}
		}
		
		private function prep_template($name, $type) {
			var_dump($name);
			$vars = get_object_vars($this);
      ob_start();
      if (file_exists($name)){
          if (count($vars)>0)
              foreach($vars as $key => $value){
                  $$key = $value;
              }
          require($name);
			}else if(empty($name)){
				return;
      } else {
          throw new NimbleException('View ['.$name.'] Not Found');
      }
      $this->_content = ob_get_clean();
		}
		
		//create headers
		private function create_headers() {
				$headers  = '';
				$headers  = "MIME-Version: 1.0\r\n";
				$headers .= "From: " . $this->from . "\n";
				$headers .= "Content-Type: multipart/alternative; boundary=\"" . $this->_divider . "\"; charset=windows-1252\n" .
							"Content-Transfer-Encoding: binary\n";

				if(isset($this->headers) && !empty($this->headers)) $headers .= $this->headers;

				return $headers;

			}
			
			public function output_message() {
				if (isset($this->html_message) && !empty($this->html_message))
				{
					$html_message = $this->parse_and_replace_message($this->html_message);

					$html_message =  "--" . $this->_divider . 
									"\nContent-Disposition: inline\n" .
									"Content-Transfer-Encoding: 8bit\n" . 
									"Content-Type: text/html\n" .
									"Content-length: " . strlen($html_message) . "\n\n" . $html_message . "\n";
				}
				if (isset($this->text_message)) 
				{
					$text_message = $this->parse_and_replace_message($this->text_message);

					$text_message = "This is a multi-part message in MIME format.\n\n" . "--" . $this->_divider . 
									"\nContent-Disposition: inline\n" .
									"Content-Transfer-Encoding: 8bit\n" .
									"Content-Type: text/plain\n" .
									"Content-length: " . strlen($text_message) . "\n\n". $text_message;
				}				


				$message = '';
				if(!empty($text_message)) $message .= $text_message;
				if(!empty($html_message)) $message .= "\n\n" . $html_message . "\n" . "--" . $this->_divider . "--";


				return $message;
			}
		
		
		private function do_mail() {
			
		}
		
	}

?>